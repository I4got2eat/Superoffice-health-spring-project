<?php

namespace App\Service;

class ChallengeService
{
    private const START_DATE = '2026-02-24';
    private const END_DATE = '2026-05-31';

    public function getStartDate(): \DateTimeInterface
    {
        return new \DateTime(self::START_DATE);
    }

    public function getEndDate(): \DateTimeInterface
    {
        return new \DateTime(self::END_DATE);
    }

    // Public properties for Twig access
    public function getStartDateProperty(): \DateTimeInterface
    {
        return $this->getStartDate();
    }

    public function getEndDateProperty(): \DateTimeInterface
    {
        return $this->getEndDate();
    }

    public function isChallengeActive(): bool
    {
        $today = new \DateTime('today');
        $start = $this->getStartDate();
        $end = $this->getEndDate();

        return $today >= $start && $today <= $end;
    }

    public function isDateInChallenge(\DateTimeInterface $date): bool
    {
        $start = $this->getStartDate();
        $end = $this->getEndDate();
        $dateOnly = \DateTime::createFromInterface($date)->setTime(0, 0, 0);

        return $dateOnly >= $start && $dateOnly <= $end;
    }

    /**
     * Get all months in the challenge
     * @return array Array of ['year' => int, 'month' => int, 'name' => string]
     */
    public function getChallengeMonths(): array
    {
        return [
            ['year' => 2026, 'month' => 2, 'name' => 'February'],
            ['year' => 2026, 'month' => 3, 'name' => 'March'],
            ['year' => 2026, 'month' => 4, 'name' => 'April'],
            ['year' => 2026, 'month' => 5, 'name' => 'May'],
        ];
    }
}
