<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "results")]
class Results
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Tournament::class, inversedBy: "results")]
    #[ORM\JoinColumn(name: "tournamentId", referencedColumnName: "tournamentId", nullable: false, onDelete: "CASCADE")]
    private Tournament $tournament;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: "results")]
    #[ORM\JoinColumn(name: "teamId", referencedColumnName: "teamId", nullable: false, onDelete: "CASCADE")]
    private Team $team;

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getTournament(): Tournament
    {
        return $this->tournament;
    }

    public function setTournament(Tournament $tournament): self
    {
        $this->tournament = $tournament;
        return $this;
    }
}