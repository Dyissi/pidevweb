<?php

namespace App\Entity;

use App\Repository\TrainingSessionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrainingSessionRepository::class)]
class TrainingSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $sessionId = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Session focus cannot be blank")]
    #[Assert\Choice(
        choices: ["Agility", "Strength", "Dribbling", "Endurance", "Sprint", "Speed"],
        message: "Invalid session focus selected"
    )]
    private ?string $sessionFocus = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "Start time cannot be blank")]
    private ?\DateTime $sessionStartTime = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Duration cannot be blank")]
    #[Assert\Positive(message: "Duration must be a positive number")]
    #[Assert\Choice(
        choices: [45, 60, 90, 120],
        message: "Duration must be one of: 45, 60, 90, or 120 minutes"
    )]
    private ?int $sessionDuration = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'session_location', referencedColumnName: 'id')]
    #[Assert\NotBlank(message: "Location cannot be blank")]
    private ?Location $location = null;

    #[ORM\Column(type: "string", length: 1250)]
    #[Assert\NotBlank(message: "Notes cannot be blank")]
    #[Assert\Length(
        max: 1250,
        maxMessage: "Notes cannot be longer than {{ limit }} characters"
    )]
    private ?string $sessionNotes = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'teamId', nullable: true)]
    private ?Team $team = null;

    public function getSessionId(): ?int
    {
        return $this->sessionId;
    }

    public function getSessionFocus(): ?string
    {
        return $this->sessionFocus;
    }

    public function setSessionFocus(?string $sessionFocus): static
    {
        $this->sessionFocus = $sessionFocus;
        return $this;
    }

    public function getSessionStartTime(): ?\DateTime
    {
        return $this->sessionStartTime;
    }

    public function setSessionStartTime(?\DateTime $time): self
    {
        $this->sessionStartTime = $time;
        return $this;
    }

    public function getSessionDuration(): ?int
    {
        return $this->sessionDuration;
    }

    public function setSessionDuration(?int $sessionDuration): static
    {
        $this->sessionDuration = $sessionDuration;
        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getSessionNotes(): ?string
    {
        return $this->sessionNotes;
    }

    public function setSessionNotes(?string $sessionNotes): static
    {
        $this->sessionNotes = $sessionNotes;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
        return $this;
    }
}