<?php

namespace App\Controller;

use App\Entity\DailyLog;
use App\Entity\WeeklyLog;
use App\Repository\DailyLogRepository;
use App\Repository\WeeklyLogRepository;
use App\Repository\UserRepository;
use App\Service\ChallengeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        Request $request,
        DailyLogRepository $dailyLogRepository,
        WeeklyLogRepository $weeklyLogRepository,
        UserRepository $userRepository,
        ChallengeService $challengeService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Calculate stats
        $totalScore = $userRepository->calculateTotalScore($user);
        $currentWeekScore = $weeklyLogRepository->getCurrentWeekPoints($user);
        $todayScore = $dailyLogRepository->getTodayPoints($user);

        // Get current week log or create placeholder
        $currentWeekLog = $weeklyLogRepository->getOrCreateForCurrentWeek($user);
        $entityManager->flush();

        // Get daily logs for the challenge period
        $startDate = $challengeService->getStartDate();
        $endDate = $challengeService->getEndDate();
        $dailyLogsMap = $dailyLogRepository->findByUserAsDateMap($user, $startDate, $endDate);

        // Get challenge months and enrich with keys like 2026-03
        $months = $challengeService->getChallengeMonths();
        foreach ($months as &$month) {
            $month['key'] = sprintf('%04d-%02d', $month['year'], $month['month']);
        }
        unset($month);

        // Determine which month to show on the calendar
        $selectedKey = $request->query->get('month');

        // Default to current month (clamped to challenge range)
        if (!$selectedKey) {
            $today = new \DateTimeImmutable('today');
            $todayKey = $today->format('Y-m');
            $selectedKey = $todayKey;
        }

        // Find index of selected month in the challenge months
        $selectedIndex = 0;
        foreach ($months as $idx => $month) {
            if ($month['key'] === $selectedKey) {
                $selectedIndex = $idx;
                break;
            }
        }

        $selectedMonth = $months[$selectedIndex];
        $prevMonth = $selectedIndex > 0 ? $months[$selectedIndex - 1] : null;
        $nextMonth = $selectedIndex < \count($months) - 1 ? $months[$selectedIndex + 1] : null;

        return $this->render('dashboard/index.html.twig', [
            'totalScore' => $totalScore,
            'currentWeekScore' => $currentWeekScore,
            'todayScore' => $todayScore,
            'currentWeekLog' => $currentWeekLog,
            'dailyLogsMap' => $dailyLogsMap,
            'months' => $months,
            'selectedMonth' => $selectedMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'challengeService' => $challengeService,
        ]);
    }

    #[Route('/dashboard/weekly-log', name: 'app_dashboard_weekly_log', methods: ['POST'])]
    public function updateWeeklyLog(
        Request $request,
        WeeklyLogRepository $weeklyLogRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $weeklyLog = $weeklyLogRepository->getOrCreateForCurrentWeek($user);

        $requestedActivity = $request->request->getBoolean('activity_done', false);
        $requestedSocial = $request->request->getBoolean('social_done', false);

        $initialActivity = $weeklyLog->isActivityDone();
        $initialSocial = $weeklyLog->isSocialDone();

        // Normal users can only move from not done -> done (never undo),
        // admins can freely toggle in the admin interface instead.
        if ($this->isGranted('ROLE_ADMIN')) {
            $weeklyLog->setActivityDone($requestedActivity);
            $weeklyLog->setSocialDone($requestedSocial);
        } else {
            $weeklyLog->setActivityDone($initialActivity || $requestedActivity);
            $weeklyLog->setSocialDone($initialSocial || $requestedSocial);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/dashboard/log/{date}', name: 'app_dashboard_log_date', methods: ['GET', 'POST'])]
    public function logDate(
        string $date,
        Request $request,
        DailyLogRepository $dailyLogRepository,
        ChallengeService $challengeService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $logDate = new \DateTime($date);
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Invalid date format');
        }

        // Only allow editing for the day before today
        $today = new \DateTimeImmutable('today');
        $yesterday = $today->modify('-1 day');
        if ($logDate->format('Y-m-d') !== $yesterday->format('Y-m-d')) {
            throw $this->createNotFoundException('Date is not editable');
        }

        // Check if date is within challenge period
        if (!$challengeService->isDateInChallenge($logDate)) {
            throw $this->createNotFoundException('Date is outside challenge period');
        }

        // Get or create daily log
        $dailyLog = $dailyLogRepository->findOneBy([
            'user' => $user,
            'date' => $logDate
        ]);

        if (!$dailyLog) {
            $dailyLog = new DailyLog();
            $dailyLog->setUser($user);
            $dailyLog->setDate($logDate);
            $entityManager->persist($dailyLog);
        }

        if ($request->isMethod('POST')) {
            $dailyLog->setHydrationDone($request->request->getBoolean('hydration_done', false));
            $dailyLog->setSleepDone($request->request->getBoolean('sleep_done', false));
            $dailyLog->setStepsDone($request->request->getBoolean('steps_done', false));
            $dailyLog->setNutritionDone($request->request->getBoolean('nutrition_done', false));

            $entityManager->flush();

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('dashboard/log_date.html.twig', [
            'dailyLog' => $dailyLog,
            'date' => $logDate,
        ]);
    }
}
