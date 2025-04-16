<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Team
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $teamId;

    #[ORM\Column(type: "string", length: 255)]
    private string $teamName;

    #[ORM\Column(type: "integer")]
    private int $teamNbAthletes;

    #[ORM\Column(type: "string", length: 255)]
    private string $teamTypeOfSport;

    #[ORM\Column(type: "integer")]
    private int $teamWins;

    #[ORM\Column(type: "integer")]
    private int $teamLosses;

    public function getTeamId()
    {
        return $this->teamId;
    }

    public function setTeamId($value)
    {
        $this->teamId = $value;
    }

    public function getTeamName()
    {
        return $this->teamName;
    }

    public function setTeamName($value)
    {
        $this->teamName = $value;
    }

    public function getTeamNbAthletes()
    {
        return $this->teamNbAthletes;
    }

    public function setTeamNbAthletes($value)
    {
        $this->teamNbAthletes = $value;
    }

    public function getTeamTypeOfSport()
    {
        return $this->teamTypeOfSport;
    }

    public function setTeamTypeOfSport($value)
    {
        $this->teamTypeOfSport = $value;
    }

    public function getTeamWins()
    {
        return $this->teamWins;
    }

    public function setTeamWins($value)
    {
        $this->teamWins = $value;
    }

    public function getTeamLosses()
    {
        return $this->teamLosses;
    }

    public function setTeamLosses($value)
    {
        $this->teamLosses = $value;
    }
}
