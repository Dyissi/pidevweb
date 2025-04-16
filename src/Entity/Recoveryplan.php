<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use App\Entity\Injury;

#[ORM\Entity]
class Recoveryplan
{
   #[ORM\Id]
#[ORM\GeneratedValue(strategy: 'AUTO')]
#[ORM\Column(name: 'recovery_id', type: 'integer')]
private ?int $recoveryId = null;


    #[ORM\ManyToOne(targetEntity: Injury::class, inversedBy: "recoveryplans")]
    #[ORM\JoinColumn(name: 'injury_id', referencedColumnName: 'injury_id', onDelete: 'CASCADE', nullable: true)]
    private ?Injury $injury = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "recoveryplans")]
    #[Assert\NotBlank(message: "user should not be blank.")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'recovery_goal', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Recovery goal should not be blank.")]
    private string $recoveryGoal; 

    #[ORM\Column(name: 'recovery_description', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Recovery description must not be blank")]
    private string $recoveryDescription; 


    #[ORM\Column(name: 'recovery_StartDate', type: 'date')]
    #[Assert\NotBlank(message: "Start date is required.")]
    #[Assert\Type("\DateTimeInterface")]
    private \DateTimeInterface $recoveryStartDate; 

    #[ORM\Column(name: 'recovery_EndDate', type: 'date')]
#[Assert\NotBlank(message: "Start date is required.")]
private ?\DateTimeInterface $recoveryEndDate = null;


    
    #[ORM\Column(name: 'recovery_Status', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Recovery status must be selected.")]
    private string $recoveryStatus; 

    
    public function __construct()
    {
        
        $this->user = null;
    }
    public function getRecoveryId(): int
    {
        return $this->recoveryId;
    }

    public function setRecoveryId(int $value): self
    {
        $this->recoveryId = $value;
        return $this;
    }

    public function getInjury(): ?Injury
    {
        return $this->injury;
    }

    public function setInjury(?Injury $injury): self
    {
        $this->injury = $injury;
        return $this;
    }

    public function getUser(): ?User 
    {
        return $this->user;
    }


    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getRecoveryGoal(): ?string
{
    return $this->recoveryGoal;
}

public function setRecoveryGoal(string $recoveryGoal): self
{
    $this->recoveryGoal = $recoveryGoal;
    return $this;
}

    public function getRecoveryDescription(): string
    {
        return $this->recoveryDescription;
    }

    public function setRecoveryDescription(string $value): self
    {
        $this->recoveryDescription = $value;
        return $this;
    }

    public function getRecoveryStartDate(): \DateTimeInterface
    {
        return $this->recoveryStartDate;
    }

    public function setRecoveryStartDate(\DateTimeInterface $value): self
    {
        $this->recoveryStartDate = $value;
        return $this;
    }

    public function getRecoveryEndDate(): \DateTimeInterface
    {
        return $this->recoveryEndDate;
    }

    public function setRecoveryEndDate(\DateTimeInterface $value): self
    {
        $this->recoveryEndDate = $value;
        return $this;
    }

    public function getRecoveryStatus(): string
    {
        return $this->recoveryStatus;
    }

    public function setRecoveryStatus(string $value): self
    {
        $this->recoveryStatus = $value;
        return $this;
    }
}
