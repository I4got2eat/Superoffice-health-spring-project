<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\DailyLogRepository;
use App\Repository\WeeklyLogRepository;
use App\Service\ChallengeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/new', name: 'admin_users_new')]
    public function newUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $email = trim((string) $request->request->get('work_email', ''));
            $dobRaw = trim((string) $request->request->get('login_password', ''));
            $isAdmin = (bool) $request->request->get('is_admin', false);

            $user = new User();
            $user->setName($name);
            $user->setWorkEmail($email);

            if ($dobRaw !== '' && preg_match('#^\d{4}-\d{2}-\d{2}$#', $dobRaw)) {
                $user->setLoginPassword(str_replace('-', '', $dobRaw));
            }

            $user->setIsAdmin($isAdmin);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'user' => null,
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_users_edit')]
    public function editUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $email = trim((string) $request->request->get('work_email', ''));
            $dobRaw = trim((string) $request->request->get('login_password', ''));
            $isAdmin = (bool) $request->request->get('is_admin', false);

            $user->setName($name);
            $user->setWorkEmail($email);

            if ($dobRaw !== '' && preg_match('#^\d{4}-\d{2}-\d{2}$#', $dobRaw)) {
                $user->setLoginPassword(str_replace('-', '', $dobRaw));
            }

            $user->setIsAdmin($isAdmin);

            $entityManager->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/users/{id}/schedule', name: 'admin_users_schedule')]
    public function userSchedule(
        int $id,
        Request $request,
        UserRepository $userRepository,
        DailyLogRepository $dailyLogRepository,
        WeeklyLogRepository $weeklyLogRepository,
        ChallengeService $challengeService
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $totalScore = $userRepository->calculateTotalScore($user);
        $currentWeekScore = $weeklyLogRepository->getCurrentWeekPoints($user);
        $todayScore = $dailyLogRepository->getTodayPoints($user);

        $startDate = $challengeService->getStartDate();
        $endDate = $challengeService->getEndDate();
        $dailyLogsMap = $dailyLogRepository->findByUserAsDateMap($user, $startDate, $endDate);

        $months = $challengeService->getChallengeMonths();
        foreach ($months as &$month) {
            $month['key'] = sprintf('%04d-%02d', $month['year'], $month['month']);
        }
        unset($month);

        $selectedKey = $request->query->get('month');
        if (!$selectedKey) {
            $today = new \DateTimeImmutable('today');
            $selectedKey = $today->format('Y-m');
        }

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

        return $this->render('admin/user_schedule.html.twig', [
            'subjectUser' => $user,
            'totalScore' => $totalScore,
            'currentWeekScore' => $currentWeekScore,
            'todayScore' => $todayScore,
            'dailyLogsMap' => $dailyLogsMap,
            'months' => $months,
            'selectedMonth' => $selectedMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'challengeService' => $challengeService,
        ]);
    }

    #[Route('/admin/users/{id}/weekly-log', name: 'admin_users_weekly_log', methods: ['POST'])]
    public function adminUpdateWeeklyLog(
        int $id,
        Request $request,
        UserRepository $userRepository,
        WeeklyLogRepository $weeklyLogRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $weeklyLog = $weeklyLogRepository->getOrCreateForCurrentWeek($user);

        $weeklyLog->setActivityDone($request->request->getBoolean('activity_done', false));
        $weeklyLog->setSocialDone($request->request->getBoolean('social_done', false));

        $entityManager->flush();

        return $this->redirectToRoute('admin_users_schedule', ['id' => $id]);
    }
}

