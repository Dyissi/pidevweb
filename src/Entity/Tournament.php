<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: "tournament")]
class Tournament
{
    #[ORM\Id]
    #[ORM\Column(name: "tournamentId", type: "integer")]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: "tournamentName", type: "string", length: 255)]
    private string $tournamentName;

    #[ORM\Column(name: "tournamentStartDate", type: "date")]
    private \DateTimeInterface $tournamentStartDate;

    #[ORM\Column(name: "tournamentEndDate", type: "date")]
    private \DateTimeInterface $tournamentEndDate;

    #[ORM\Column(name: "tournamentLocation", type: "string", length: 255)]
    private string $tournamentLocation;

    #[ORM\Column(name: "tournamentTOS", type: "string", length: 255)]
    private string $tournamentTOS;

    #[ORM\Column(name: "tournamentNbteams", type: "integer")]
    private int $tournamentNbteams = 0; // Default to 0

    #[ORM\Column(name: "tournamentWinner", type: "integer", nullable: true)]
    private ?int $tournamentWinner = null;

    #[ORM\OneToMany(mappedBy: "tournament", targetEntity: Results::class, orphanRemoval: true)]
    private Collection $results;

    public function __construct()
    {
        $this->results = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTournamentName(): string
    {
        return $this->tournamentName;
    }

    public function setTournamentName(string $tournamentName): self
    {
        $this->tournamentName = $tournamentName;
        return $this;
    }

    public function getTournamentStartDate(): \DateTimeInterface
    {
        return $this->tournamentStartDate;
    }

    public function setTournamentStartDate(\DateTimeInterface $tournamentStartDate): self
    {
        $this->tournamentStartDate = $tournamentStartDate;
        return $this;
    }

    public function getTournamentEndDate(): \DateTimeInterface
    {
        return $this->tournamentEndDate;
    }

    public function setTournamentEndDate(\DateTimeInterface $tournamentEndDate): self
    {
        $this->tournamentEndDate = $tournamentEndDate;
        return $this;
    }

    public function getTournamentLocation(): string
    {
        return $this->tournamentLocation;
    }

    public function setTournamentLocation(string $tournamentLocation): self
    {
        $this->tournamentLocation = $tournamentLocation;
        return $this;
    }

    public function getTournamentTOS(): string
    {
        return $this->tournamentTOS;
    }

    public function setTournamentTOS(string $tournamentTOS): self
    {
        $this->tournamentTOS = $tournamentTOS;
        return $this;
    }

    public function getTournamentNbteams(): int
    {
        return $this->tournamentNbteams;
    }

    public function setTournamentNbteams(int $tournamentNbteams): self
    {
        $this->tournamentNbteams = $tournamentNbteams;
        return $this;
    }

    public function getTournamentWinner(): ?int
    {
        return $this->tournamentWinner;
    }

    public function setTournamentWinner(?int $tournamentWinner): self
    {
        $this->tournamentWinner = $tournamentWinner;
        return $this;
    }

    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(Results $result): self
    {
        if (!$this->results->contains($result)) {
            $this->results[] = $result;
            $result->setTournament($this);
        }
        return $this;
    }

    public function removeResult(Results $result): self
    {
        $this->results->removeElement($result);
        return $this;
    }
}