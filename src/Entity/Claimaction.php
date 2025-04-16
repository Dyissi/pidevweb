<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "claimaction")]
class Claimaction
{
    #[ORM\Id]
    #[ORM\Column(name: "claimActionId", type: "integer")]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: "claimActionType", type: "string")]
    private string $claimActionType;

    #[ORM\Column(name: "claimActionStartDate", type: "date")]
    private \DateTimeInterface $claimActionStartDate;

    #[ORM\Column(name: "claimActionEndDate", type: "date")]
    private \DateTimeInterface $claimActionEndDate;

    #[ORM\Column(name: "claimActionNotes", type: "string", length: 200)]
    private string $claimActionNotes;

    #[ORM\ManyToOne(targetEntity: Claim::class, inversedBy: "claimactions")]
    #[ORM\JoinColumn(
        name: "claimId", 
        referencedColumnName: "claimId",
        nullable: false,
        onDelete: "CASCADE"
    )]
    private Claim $claim;

    public function getId(): int
    {
        return $this->id;
    }

    public function getClaimActionType(): string
    {
        return $this->claimActionType;
    }

    public function setClaimActionType(string $claimActionType): self
    {
        $this->claimActionType = $claimActionType;
        return $this;
    }

    public function getClaimActionStartDate(): \DateTimeInterface
    {
        return $this->claimActionStartDate;
    }

    public function setClaimActionStartDate(\DateTimeInterface $claimActionStartDate): self
    {
        $this->claimActionStartDate = $claimActionStartDate;
        return $this;
    }

    public function getClaimActionEndDate(): \DateTimeInterface
    {
        return $this->claimActionEndDate;
    }

    public function setClaimActionEndDate(\DateTimeInterface $claimActionEndDate): self
    {
        $this->claimActionEndDate = $claimActionEndDate;
        return $this;
    }

    public function getClaimActionNotes(): string
    {
        return $this->claimActionNotes;
    }

    public function setClaimActionNotes(string $claimActionNotes): self
    {
        $this->claimActionNotes = $claimActionNotes;
        return $this;
    }

    public function getClaim(): Claim
    {
        return $this->claim;
    }

    public function setClaim(Claim $claim): self
    {
        $this->claim = $claim;
        return $this;
    }
}