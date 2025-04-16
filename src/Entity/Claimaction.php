<?php

namespace App\Entity;

use App\Entity\Claim;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
class Claimaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "claimActionId", type: "integer")]
    private int $claimActionId;

    #[ORM\Column(name: "claimActionType", type: "string", length: 20)]
    #[Assert\NotBlank]
    private string $claimActionType;

    #[ORM\Column(name: "claimActionStartDate", type: "date")]
    #[Assert\NotBlank]
    private \DateTimeInterface $claimActionStartDate;

    #[ORM\Column(name: "claimActionEndDate", type: "date")]
    #[Assert\NotBlank]
    private \DateTimeInterface $claimActionEndDate;

    #[ORM\Column(name: "claimActionNotes", type: "string", length: 200)]
    #[Assert\NotBlank]
    private string $claimActionNotes;

    #[ORM\ManyToOne(targetEntity: Claim::class, inversedBy: "claimactions")]
    #[ORM\JoinColumn(name: 'claimId', referencedColumnName: 'claimId', onDelete: 'CASCADE')]
    private ?Claim $claim = null; // ✅ Fix: nullable relation with default null

    // ─── Getters & Setters ────────────────────────────────────────

    public function getClaimActionId(): int
    {
        return $this->claimActionId;
    }

    public function setClaimActionId(int $value): self
    {
        $this->claimActionId = $value;
        return $this;
    }

    public function getClaimActionType(): string
    {
        return $this->claimActionType;
    }

    public function setClaimActionType(string $value): self
    {
        $this->claimActionType = $value;
        return $this;
    }

    public function getClaimActionStartDate(): \DateTimeInterface
    {
        return $this->claimActionStartDate;
    }

    public function setClaimActionStartDate(\DateTimeInterface $value): self
    {
        $this->claimActionStartDate = $value;
        return $this;
    }

    public function getClaimActionEndDate(): \DateTimeInterface
    {
        return $this->claimActionEndDate;
    }

    public function setClaimActionEndDate(\DateTimeInterface $value): self
    {
        $this->claimActionEndDate = $value;
        return $this;
    }

    public function getClaimActionNotes(): string
    {
        return $this->claimActionNotes;
    }

    public function setClaimActionNotes(string $value): self
    {
        $this->claimActionNotes = $value;
        return $this;
    }

    public function getClaim(): ?Claim // ✅ Nullable return
    {
        return $this->claim;
    }

    public function setClaim(?Claim $value): self
    {
        $this->claim = $value;
        return $this;
    }

    // ─── Validation Callbacks ─────────────────────────────────────

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context, $payload): void
    {
        if ($this->claimActionStartDate && $this->claimActionEndDate) {
            if ($this->claimActionStartDate > $this->claimActionEndDate) {
                $context->buildViolation('Start date must be before or equal to the end date.')
                    ->atPath('claimActionStartDate')
                    ->addViolation();
            }
        }
    }

    #[Assert\Callback]
    public function validateNotes(ExecutionContextInterface $context, $payload): void
    {
        $length = strlen(trim((string) $this->claimActionNotes));
        if ($length < 10 || $length > 200) {
            $context->buildViolation('Notes must be between 10 and 200 characters long.')
                ->atPath('claimActionNotes')
                ->addViolation();
        }
    }
}
