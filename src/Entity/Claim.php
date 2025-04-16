<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Claim
{
    #[ORM\Id]
    #[ORM\Column(name: "claimId", type: "integer")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $claimId;

    #[ORM\Column(name: "claimDescription", type: "string", length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private string $claimDescription;

    #[ORM\Column(name: "claimStatus", type: "string", length: 20)]
    #[Assert\NotBlank]
    private string $claimStatus;

    #[ORM\Column(name: "claimDate", type: "date")]
    #[Assert\NotBlank]
    private \DateTimeInterface $claimDate;

    #[ORM\Column(name: "claimCategory", type: "string", length: 20)]
    #[Assert\NotBlank]
    private string $claimCategory;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "submittedClaims")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "user_id", nullable: false)]
    private User $id_user;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "receivedClaims")]
    #[ORM\JoinColumn(name: "id_user_to_claim", referencedColumnName: "user_id", nullable: false)]
    private User $id_user_to_claim;
    

    #[ORM\OneToMany(mappedBy: "claim", targetEntity: Claimaction::class)]
    private Collection $claimactions;

    public function __construct()
    {
        $this->claimactions = new ArrayCollection();
    }

    // Getters and Setters
    public function getClaimId(): int
    {
        return $this->claimId;
    }

    public function setClaimId(int $claimId): self
    {
        $this->claimId = $claimId;
        return $this;
    }

    public function getClaimDescription(): string
    {
        return $this->claimDescription;
    }

    public function setClaimDescription(string $claimDescription): self
    {
        $this->claimDescription = $claimDescription;
        return $this;
    }

    public function getClaimStatus(): string
    {
        return $this->claimStatus;
    }

    public function setClaimStatus(string $claimStatus): self
    {
        $this->claimStatus = $claimStatus;
        return $this;
    }

    public function getClaimDate(): \DateTimeInterface
    {
        return $this->claimDate;
    }

    public function setClaimDate(\DateTimeInterface $claimDate): self
    {
        $this->claimDate = $claimDate;
        return $this;
    }

    public function getClaimCategory(): string
    {
        return $this->claimCategory;
    }

    public function setClaimCategory(string $claimCategory): self
    {
        $this->claimCategory = $claimCategory;
        return $this;
    }

    public function getIdUser(): User
    {
        return $this->id_user;
    }

    public function setIdUser(User $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }

    public function getIdUserToClaim(): User
    {
        return $this->id_user_to_claim;
    }

    public function setIdUserToClaim(User $id_user_to_claim): self
    {
        $this->id_user_to_claim = $id_user_to_claim;
        return $this;
    }

    public function getClaimactions(): Collection
    {
        return $this->claimactions;
    }

    public function addClaimaction(Claimaction $claimaction): self
    {
        if (!$this->claimactions->contains($claimaction)) {
            $this->claimactions[] = $claimaction;
            $claimaction->setClaim($this);
        }
        return $this;
    }

    public function removeClaimaction(Claimaction $claimaction): self
    {
        if ($this->claimactions->removeElement($claimaction)) {
            // set the owning side to null (unless already changed)
            if ($claimaction->getClaim() === $this) {
                $claimaction->setClaim(null);
            }
        }
        return $this;
    }

    public function hasActionSubmitted(): bool
    {
        return !$this->claimactions->isEmpty();
    }

}