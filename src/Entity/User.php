<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'unique_name', columns: ['name'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $workEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $loginPassword = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isAdmin = false;

    #[ORM\OneToMany(targetEntity: DailyLog::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $dailyLogs;

    #[ORM\OneToMany(targetEntity: WeeklyLog::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $weeklyLogs;

    public function __construct()
    {
        $this->dailyLogs = new ArrayCollection();
        $this->weeklyLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getWorkEmail(): ?string
    {
        return $this->workEmail;
    }

    public function setWorkEmail(?string $workEmail): static
    {
        $this->workEmail = $workEmail ? strtolower($workEmail) : null;
        return $this;
    }

    public function getLoginPassword(): ?string
    {
        return $this->loginPassword;
    }

    public function setLoginPassword(?string $loginPassword): static
    {
        $this->loginPassword = $loginPassword;
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    /**
     * @return Collection<int, DailyLog>
     */
    public function getDailyLogs(): Collection
    {
        return $this->dailyLogs;
    }

    public function addDailyLog(DailyLog $dailyLog): static
    {
        if (!$this->dailyLogs->contains($dailyLog)) {
            $this->dailyLogs->add($dailyLog);
            $dailyLog->setUser($this);
        }

        return $this;
    }

    public function removeDailyLog(DailyLog $dailyLog): static
    {
        if ($this->dailyLogs->removeElement($dailyLog)) {
            if ($dailyLog->getUser() === $this) {
                $dailyLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WeeklyLog>
     */
    public function getWeeklyLogs(): Collection
    {
        return $this->weeklyLogs;
    }

    public function addWeeklyLog(WeeklyLog $weeklyLog): static
    {
        if (!$this->weeklyLogs->contains($weeklyLog)) {
            $this->weeklyLogs->add($weeklyLog);
            $weeklyLog->setUser($this);
        }

        return $this;
    }

    public function removeWeeklyLog(WeeklyLog $weeklyLog): static
    {
        if ($this->weeklyLogs->removeElement($weeklyLog)) {
            if ($weeklyLog->getUser() === $this) {
                $weeklyLog->setUser(null);
            }
        }

        return $this;
    }

    // UserInterface methods
    public function getUserIdentifier(): string
    {
        // Must match the property used by the security provider (workEmail)
        return (string) ($this->workEmail ?? $this->name);
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        if ($this->isAdmin) {
            $roles[] = 'ROLE_ADMIN';
        }
        return $roles;
    }

    public function eraseCredentials(): void
    {
        // No credentials to erase for name-based auth
    }

    public function getPassword(): ?string
    {
        return $this->loginPassword;
    }
}
