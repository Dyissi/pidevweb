<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Recoveryplan;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity]
#[Vich\Uploadable] 
class Injury
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "injury_id", type: "integer")]
    private int $injury_id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "injuries")]
    #[Assert\NotBlank(message: "user should not be blank.")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(name: "injuryType", type: "string", length: 50)]
    #[Assert\NotBlank(message: "Injury Type should not be blank.")]
    private string $injuryType;

    #[ORM\Column(name: "injury_date", type: "date", nullable: false)]
    private \DateTimeInterface $injury_date;

    #[ORM\Column(name: "injury_severity", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Injury Severity should not be blank.")]
    private string $injury_severity;

    #[ORM\Column(name: "injury_description", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Injury description must not be blank")]
    private string $injury_description;

    #[Vich\UploadableField(
        mapping: "injury_images",
        fileNameProperty: "image", 
        
    )]
    private ?File $imageFile = null;

    #[ORM\Column(name: "image", type: "string", length: 255, nullable: true)]
    private ?string $image = null;  

    #[ORM\OneToMany(targetEntity: Recoveryplan::class, mappedBy: 'injury')]
    private Collection $recoveryplans;

    public function __construct()
    {
        $this->recoveryplans = new ArrayCollection();
        $this->injury_date = new \DateTimeImmutable();
    }

    public function getInjuryId(): int
    {
        return $this->injury_id;
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
        return $this->injury_date;
    }

    public function setInjuryDate(\DateTimeInterface $injury_date): self
    {
        $this->injury_date = $injury_date;
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

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
      
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }
    /**
     * @return Collection<int, Recoveryplan>
     */
    public function getRecoveryplans(): Collection
    {
        return $this->recoveryplans;
    }

    public function addRecoveryplan(Recoveryplan $recoveryplan): self
    {
        if (!$this->recoveryplans->contains($recoveryplan)) {
            $this->recoveryplans[] = $recoveryplan;
            $recoveryplan->setInjury($this);
        }
        return $this;
    }

    public function removeRecoveryplan(Recoveryplan $recoveryplan): self
    {
        if ($this->recoveryplans->removeElement($recoveryplan)) {
            if ($recoveryplan->getInjury() === $this) {
                $recoveryplan->setInjury(null);
            }
        }
        return $this;
    }
}