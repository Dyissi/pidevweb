<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Recoveryplan;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
class Injury
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "injury_id", type: "integer")]
    private int $injury_id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "injuries")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(name: "injuryType", type: "string", length: 50)]
    #[Assert\NotBlank(message: "Injury Type should not be blank.")]
    private string $injuryType;

    #[ORM\Column(name: "injury_date", type: "date", nullable: false)]
    private ?\DateTimeInterface $injury_date = null;


    #[ORM\Column(name: "injury_severity", type: "string", length: 50)]
    #[Assert\NotBlank(message: "Injury Severity should not be blank.")]

    private string $injury_severity;

    #[ORM\Column(name: "injury_description", type: "text")]
    #[Assert\NotBlank(message: "Injury description must not be blank")]

    private string $injury_description;

    #[ORM\Column(name: "image", type: "string", length: 255, nullable: true)]
    private ?string $imagePath = null;

    // One-to-many relationship with Recoveryplan
    #[ORM\OneToMany(targetEntity: Recoveryplan::class, mappedBy: 'injury')]
    private Collection $recoveryplans;

    public function __construct()
    {
        $this->recoveryplans = new ArrayCollection();
    }

    public function getInjuryId(): int
    {
        return $this->injury_id;
    }

    public function setInjuryId(int $injury_id): self
    {
        $this->injury_id = $injury_id;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getInjuryType(): string
    {
        return $this->injuryType;
    }

    public function setInjuryType(string $injuryType): self
    {
        $this->injuryType = $injuryType;
        return $this;
    }

    public function getInjuryDate(): \DateTimeInterface
    {
        return $this->injuryDate;
    }

    public function setInjuryDate(\DateTimeInterface $injuryDate): self
    {
        $this->injuryDate = $injuryDate;
        return $this;
    }

    public function getInjurySeverity(): string
    {
        return $this->injury_severity;
    }

    public function setInjurySeverity(string $injury_severity): self
    {
        $this->injury_severity = $injury_severity;
        return $this;
    }

    public function getInjuryDescription(): string
    {
        return $this->injury_description;
    }

    public function setInjuryDescription(string $injury_description): self
    {
        $this->injury_description = $injury_description;
        return $this;
    }
}
