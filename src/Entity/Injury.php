<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "injury")]
class Injury
{
    #[ORM\Id]
    #[ORM\Column(name: "injury_id", type: "integer")]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "injuries")]
    #[ORM\JoinColumn(
        name: "user_id",
        referencedColumnName: "user_id",  // Must match User's ID column name
        nullable: false,
        onDelete: "CASCADE"
    )]
    private User $user;

    #[ORM\Column(name: "injuryType", type: "string", length: 255)]
    private string $injuryType;

    #[ORM\Column(name: "injuryDate", type: "date")]
    private \DateTimeInterface $injuryDate;

    #[ORM\Column(name: "injury_severity", type: "string", length: 50)]
    private string $injury_severity;

    #[ORM\Column(name: "injury_description", type: "text")]
    private string $injury_description;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
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