<?php

namespace App\Repository;

use App\Entity\DailyLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DailyLog>
 */
class DailyLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyLog::class);
    }

    /**
     * Get total points for a user from all daily logs
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
     * Get points for a specific date
     */
    public function getPointsForDate(User $user, \DateTimeInterface $date): int
    {
        $log = $this->findOneBy([
            'user' => $user,
            'date' => $date
        ]);

        return $log ? $log->getPoints() : 0;
    }

    /**
     * Get points for today
     */
    public function getTodayPoints(User $user): int
    {
        $today = new \DateTime('today');
        return $this->getPointsForDate($user, $today);
    }

    /**
     * Get all daily logs for a user within date range
     */
    public function findByUserAndDateRange(User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.date >= :startDate')
            ->andWhere('d.date <= :endDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('d.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get daily logs for a user as an associative array keyed by date string (Y-m-d)
     */
    public function findByUserAsDateMap(User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $logs = $this->findByUserAndDateRange($user, $startDate, $endDate);
        $map = [];

        foreach ($logs as $log) {
            $dateKey = $log->getDate()->format('Y-m-d');
            $map[$dateKey] = $log;
        }

        return $map;
    }
}
