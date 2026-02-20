<?php

namespace App\Entity;

use App\Repository\WeeklyLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeeklyLogRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_user_week', columns: ['user_id', 'week_start_date'])]
#[ORM\Table(name: 'weekly_log')]
class WeeklyLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'weeklyLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $weekStartDate = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $activityDone = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $socialDone = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getWeekStartDate(): ?\DateTimeInterface
    {
        return $this->weekStartDate;
    }

    public function setWeekStartDate(\DateTimeInterface $weekStartDate): static
    {
        $this->weekStartDate = $weekStartDate;
        return $this;
    }

    public function isActivityDone(): bool
    {
        return $this->activityDone;
    }

    public function setActivityDone(bool $activityDone): static
    {
        $this->activityDone = $activityDone;
        return $this;
    }

    public function isSocialDone(): bool
    {
        return $this->socialDone;
    }

    public function setSocialDone(bool $socialDone): static
    {
        $this->socialDone = $socialDone;
        return $this;
    }

    /**
     * Calculate points for this weekly log entry
     */
    public function getPoints(): int
    {
        $points = 0;
        if ($this->activityDone) $points++;
        if ($this->socialDone) $points++;
        return $points;
    }
}
