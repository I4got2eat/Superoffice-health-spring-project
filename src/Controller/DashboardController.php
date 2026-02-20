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

        // Get challenge months
        $months = $challengeService->getChallengeMonths();

        return $this->render('dashboard/index.html.twig', [
            'totalScore' => $totalScore,
            'currentWeekScore' => $currentWeekScore,
            'todayScore' => $todayScore,
            'currentWeekLog' => $currentWeekLog,
            'dailyLogsMap' => $dailyLogsMap,
            'months' => $months,
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
        
        $weeklyLog->setActivityDone($request->request->getBoolean('activity_done', false));
        $weeklyLog->setSocialDone($request->request->getBoolean('social_done', false));

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
