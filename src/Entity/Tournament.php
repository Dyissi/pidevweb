<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Tournament
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $tournamentId;

    #[ORM\Column(type: "string", length: 255)]
    private string $tournamentName;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $tournamentStartDate;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $tournamentEndDate;

    #[ORM\Column(type: "string", length: 255)]
    private string $tournamentLocation;

    #[ORM\Column(type: "string", length: 255)]
    private string $tournamentTOS;

    #[ORM\Column(type: "integer")]
    private int $tournamentNbteams;

    public function getTournamentId()
    {
        return $this->tournamentId;
    }

    public function setTournamentId($value)
    {
        $this->tournamentId = $value;
    }

    public function getTournamentName()
    {
        return $this->tournamentName;
    }

    public function setTournamentName($value)
    {
        $this->tournamentName = $value;
    }

    public function getTournamentStartDate()
    {
        return $this->tournamentStartDate;
    }

    public function setTournamentStartDate($value)
    {
        $this->tournamentStartDate = $value;
    }

    public function getTournamentEndDate()
    {
        return $this->tournamentEndDate;
    }

    public function setTournamentEndDate($value)
    {
        $this->tournamentEndDate = $value;
    }

    public function getTournamentLocation()
    {
        return $this->tournamentLocation;
    }

    public function setTournamentLocation($value)
    {
        $this->tournamentLocation = $value;
    }

    public function getTournamentTOS()
    {
        return $this->tournamentTOS;
    }

    public function setTournamentTOS($value)
    {
        $this->tournamentTOS = $value;
    }

    public function getTournamentNbteams()
    {
        return $this->tournamentNbteams;
    }

    public function setTournamentNbteams($value)
    {
        $this->tournamentNbteams = $value;
    }
}
