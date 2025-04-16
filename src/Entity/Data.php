<?php

namespace App\Entity;

use App\Repository\DataRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DataRepository::class)]
#[ORM\Table(name: 'data')]
class Data
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $performanceId = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Speed cannot be blank")]
    #[Assert\PositiveOrZero(message: "Speed must be zero or positive")]
    #[Assert\LessThanOrEqual(100, message: "Speed cannot exceed 100 km/h")]
    private ?float $performanceSpeed = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Agility cannot be blank")]
    #[Assert\PositiveOrZero(message: "Agility must be zero or positive")]
    private ?float $performanceAgility = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Number of goals cannot be blank")]
    #[Assert\PositiveOrZero(message: "Number of goals must be zero or positive")]
    private ?int $performanceNbrGoals = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Assists cannot be blank")]
    #[Assert\PositiveOrZero(message: "Assists must be zero or positive")]
    private ?int $performanceAssists = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: "Date cannot be blank")]
    #[Assert\LessThanOrEqual("today", message: "Date cannot be in the future")]
    private ?\DateTimeInterface $performanceDateRecorded = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Number of fouls cannot be blank")]
    #[Assert\PositiveOrZero(message: "Number of fouls must be zero or positive")]
    private ?int $performanceNbrFouls = null;

    #[ORM\Column(type: "integer")]
    private ?int $userId = null;

    public function getPerformanceId(): ?int
    {
        return $this->performanceId;
    }

    // public function setPerformanceId(?int $performanceId): static
    // {
    //     $this->performanceId = $performanceId;
    //     return $this;
    // }

    public function getPerformanceSpeed(): ?float
    {
        return $this->performanceSpeed;
    }

    public function setPerformanceSpeed(?float $performanceSpeed): static
    {
        $this->performanceSpeed = $performanceSpeed;
        return $this;
    }

    public function getPerformanceAgility(): ?float
    {
        return $this->performanceAgility;
    }

    public function setPerformanceAgility(?float $performanceAgility): static
    {
        $this->performanceAgility = $performanceAgility;
        return $this;
    }

    public function getPerformanceNbrGoals(): ?int
    {
        return $this->performanceNbrGoals;
    }

    public function setPerformanceNbrGoals(?int $performanceNbrGoals): static
    {
        $this->performanceNbrGoals = $performanceNbrGoals;
        return $this;
    }

    public function getPerformanceAssists(): ?int
    {
        return $this->performanceAssists;
    }

    public function setPerformanceAssists(?int $performanceAssists): static
    {
        $this->performanceAssists = $performanceAssists;
        return $this;
    }

    public function getPerformanceDateRecorded(): ?\DateTimeInterface
    {
        return $this->performanceDateRecorded;
    }

    public function setPerformanceDateRecorded(?\DateTimeInterface $performanceDateRecorded): static
    {
        $this->performanceDateRecorded = $performanceDateRecorded;
        return $this;
    }

    public function getPerformanceNbrFouls(): ?int
    {
        return $this->performanceNbrFouls;
    }

    public function setPerformanceNbrFouls(?int $performanceNbrFouls): static
    {
        $this->performanceNbrFouls = $performanceNbrFouls;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }
}
