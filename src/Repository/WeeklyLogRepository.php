<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WeeklyLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeeklyLog>
 */
class WeeklyLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeeklyLog::class);
    }

    /**
     * Get total points for a user from all weekly logs
     */
    public function getTotalPointsForUser(User $user): int
    {
        $logs = $this->findBy(['user' => $user]);
        $total = 0;

        foreach ($logs as $log) {
            $total += $log->getPoints();
        }

        return $total;
    }

    /**
     * Get points for current week
     */
    public function getCurrentWeekPoints(User $user): int
    {
        $weekStart = $this->getCurrentWeekStart();
        $log = $this->findOneBy([
            'user' => $user,
            'weekStartDate' => $weekStart
        ]);

        return $log ? $log->getPoints() : 0;
    }

    /**
     * Get current week start date (Monday)
     */
    public function getCurrentWeekStart(): \DateTimeInterface
    {
        $today = new \DateTime('today');
        $dayOfWeek = (int)$today->format('N'); // 1 (Monday) to 7 (Sunday)
        $daysToMonday = $dayOfWeek - 1;

        $monday = clone $today;
        $monday->modify("-{$daysToMonday} days");

        return $monday;
    }

    /**
     * Get or create weekly log for current week
     */
    public function getOrCreateForCurrentWeek(User $user): WeeklyLog
    {
        $weekStart = $this->getCurrentWeekStart();
        $log = $this->findOneBy([
            'user' => $user,
            'weekStartDate' => $weekStart
        ]);

        if (!$log) {
            $log = new WeeklyLog();
            $log->setUser($user);
            $log->setWeekStartDate($weekStart);
            $this->getEntityManager()->persist($log);
        }

        return $log;
    }

    /**
     * Get recent weeks for a user (newest first)
     *
     * @return WeeklyLog[]
     */
    public function findRecentWeeksForUser(User $user, int $limit = 8): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('w.weekStartDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all weeks for a user in chronological order (oldest first)
     *
     * @return WeeklyLog[]
     */
    public function findAllWeeksForUser(User $user): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('w.weekStartDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
