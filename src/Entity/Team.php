<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: "team")]
class Team
{
    #[ORM\Id]
    #[ORM\Column(name: "teamId", type: "integer")]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: "teamName", type: "string", length: 255)]
    private string $teamName;

    #[ORM\Column(name: "teamNbAthletes", type: "integer")]
    private int $teamNbAthletes;

    #[ORM\Column(name: "teamTypeOfSport", type: "string", length: 255)]
    private string $teamTypeOfSport;

    #[ORM\Column(name: "teamWins", type: "integer")]
    private int $teamWins;

    #[ORM\Column(name: "teamLosses", type: "integer")]
    private int $teamLosses;

    #[ORM\OneToMany(mappedBy: "team", targetEntity: Results::class, orphanRemoval: true)]
    private Collection $results;

    #[ORM\OneToMany(mappedBy: "team", targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->results = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTeamName(): string
    {
        return $this->teamName;
    }

    public function setTeamName(string $teamName): self
    {
        $this->teamName = $teamName;
        return $this;
    }

    public function getTeamNbAthletes(): int
    {
        return $this->teamNbAthletes;
    }

    public function setTeamNbAthletes(int $teamNbAthletes): self
    {
        $this->teamNbAthletes = $teamNbAthletes;
        return $this;
    }

    public function getTeamTypeOfSport(): string
    {
        return $this->teamTypeOfSport;
    }

    public function setTeamTypeOfSport(string $teamTypeOfSport): self
    {
        $this->teamTypeOfSport = $teamTypeOfSport;
        return $this;
    }

    public function getTeamWins(): int
    {
        return $this->teamWins;
    }

    public function setTeamWins(int $teamWins): self
    {
        $this->teamWins = $teamWins;
        return $this;
    }

    public function getTeamLosses(): int
    {
        return $this->teamLosses;
    }

    public function setTeamLosses(int $teamLosses): self
    {
        $this->teamLosses = $teamLosses;
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
            $result->setTeam($this);
        }
        return $this;
    }

    public function removeResult(Results $result): self
    {
        $this->results->removeElement($result);
        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setTeam($this);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);
        return $this;
    }
}