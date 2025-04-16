<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Location name cannot be blank")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Location name cannot exceed {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s]+$/",
        message: "Location name must contain only letters and spaces"
    )]
    private ?string $locationName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Location address cannot be blank")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Location address cannot exceed {{ limit }} characters"
    )]
    private ?string $locationAddress = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Location city cannot be blank")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Location city cannot exceed {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s]+$/",
        message: "Location city must contain only letters and spaces"
    )]
    private ?string $locationCity = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Location capacity cannot be blank")]
    #[Assert\Positive(message: "Location capacity must be a positive number")]
    private ?int $locationCapacity = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Location type cannot be blank")]
    private ?string $locationType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocationName(): ?string
    {
        return $this->locationName;
    }

    public function setLocationName(string $locationName): static
    {
        $this->locationName = $locationName;

        return $this;
    }

    public function getLocationAddress(): ?string
    {
        return $this->locationAddress;
    }

    public function setLocationAddress(string $locationAddress): static
    {
        $this->locationAddress = $locationAddress;

        return $this;
    }

    public function getLocationCity(): ?string
    {
        return $this->locationCity;
    }

    public function setLocationCity(string $locationCity): static
    {
        $this->locationCity = $locationCity;

        return $this;
    }

    public function getLocationCapacity(): ?int
    {
        return $this->locationCapacity;
    }

    public function setLocationCapacity(int $locationCapacity): static
    {
        $this->locationCapacity = $locationCapacity;

        return $this;
    }

    public function getLocationType(): ?string
    {
        return $this->locationType;
    }

    public function setLocationType(string $locationType): static
    {
        $this->locationType = $locationType;

        return $this;
    }
}
