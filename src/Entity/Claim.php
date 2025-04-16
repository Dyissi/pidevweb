<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: "claim")]
class Claim
{
    #[ORM\Id]
    #[ORM\Column(name: "claimId", type: "integer")]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: "claimDescription", type: "string", length: 200)]
    private string $claimDescription;

    #[ORM\Column(name: "claimStatus", type: "string")]
    private string $claimStatus;

    #[ORM\Column(name: "claimDate", type: "date")]
    private \DateTimeInterface $claimDate;

    #[ORM\Column(name: "claimCategory", type: "string")]
    private string $claimCategory;

    #[ORM\OneToMany(
        mappedBy: "claim", 
        targetEntity: Claimaction::class,
        cascade: ["persist"],
        orphanRemoval: true
    )]
    private Collection $claimactions;

    public function __construct()
    {
        $this->claimactions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    /**
     * @return Collection|Claimaction[]
     */
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
        $this->claimactions->removeElement($claimaction);
        return $this;
    }
}