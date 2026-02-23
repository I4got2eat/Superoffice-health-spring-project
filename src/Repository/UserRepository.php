<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByName(string $name): ?User
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findByWorkEmail(string $workEmail): ?User
    {
        return $this->findOneBy(['workEmail' => strtolower($workEmail)]);
    }

    /**
     * Get all users with their total scores for leaderboard
     * @return array Array of ['user' => User, 'totalScore' => int]
     */
    public function findAllWithTotalScores(): array
    {
        $users = $this->findAll();
        $result = [];

        foreach ($users as $user) {
            $totalScore = $this->calculateTotalScore($user);
            $result[] = [
                'user' => $user,
                'totalScore' => $totalScore
            ];
        }

        // Sort by total score descending
        usort($result, function($a, $b) {
            return $b['totalScore'] <=> $a['totalScore'];
        });

        return $result;
    }

    /**
     * Calculate total score for a user
     */
    public function calculateTotalScore(User $user): int
    {
        $dailyScore = $this->getEntityManager()
            ->getRepository(\App\Entity\DailyLog::class)
            ->getTotalPointsForUser($user);

        $weeklyScore = $this->getEntityManager()
            ->getRepository(\App\Entity\WeeklyLog::class)
            ->getTotalPointsForUser($user);

        return $dailyScore + $weeklyScore;
    }
}
