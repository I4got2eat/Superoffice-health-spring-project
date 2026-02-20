<?php

namespace App\Entity;

use App\Repository\DailyLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyLogRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_user_date', columns: ['user_id', 'date'])]
#[ORM\Table(name: 'daily_log')]
class DailyLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'dailyLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $hydrationDone = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $sleepDone = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $stepsDone = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $nutritionDone = false;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function isHydrationDone(): bool
    {
        return $this->hydrationDone;
    }

    public function setHydrationDone(bool $hydrationDone): static
    {
        $this->hydrationDone = $hydrationDone;
        return $this;
    }

    public function isSleepDone(): bool
    {
        return $this->sleepDone;
    }

    public function setSleepDone(bool $sleepDone): static
    {
        $this->sleepDone = $sleepDone;
        return $this;
    }

    public function isStepsDone(): bool
    {
        return $this->stepsDone;
    }

    public function setStepsDone(bool $stepsDone): static
    {
        $this->stepsDone = $stepsDone;
        return $this;
    }

    public function isNutritionDone(): bool
    {
        return $this->nutritionDone;
    }

    public function setNutritionDone(bool $nutritionDone): static
    {
        $this->nutritionDone = $nutritionDone;
        return $this;
    }

    /**
     * Calculate points for this daily log entry
     */
    public function getPoints(): int
    {
        $points = 0;
        if ($this->hydrationDone) $points++;
        if ($this->sleepDone) $points++;
        if ($this->stepsDone) $points++;
        if ($this->nutritionDone) $points++;
        return $points;
    }
}
