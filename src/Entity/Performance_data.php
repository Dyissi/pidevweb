<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Performance_data
{
    #[ORM\Id]
    #[ORM\Column(name: "performance_id", type: "integer")]
    private int $performance_id;

    #[ORM\Column(name: "performance_speed", type: "float")]
    private float $performance_speed;

    #[ORM\Column(name: "performance_agility", type: "float")]
    private float $performance_agility;

    #[ORM\Column(name: "performance_nbr_goals", type: "integer")]
    private int $performance_nbr_goals;

    #[ORM\Column(name: "performance_assists", type: "integer")]
    private int $performance_assists;

    #[ORM\Column(name: "performance_date_recorded", type: "date")]
    private \DateTimeInterface $performance_date_recorded;

    #[ORM\Column(name: "performance_nbr_fouls", type: "integer")]
    private int $performance_nbr_fouls;

    public function getPerformance_id()
    {
        return $this->performance_id;
    }

    public function setPerformance_id($value)
    {
        $this->performance_id = $value;
    }

    public function getPerformance_speed()
    {
        return $this->performance_speed;
    }

    public function setPerformance_speed($value)
    {
        $this->performance_speed = $value;
    }

    public function getPerformance_agility()
    {
        return $this->performance_agility;
    }

    public function setPerformance_agility($value)
    {
        $this->performance_agility = $value;
    }

    public function getPerformance_nbr_goals()
    {
        return $this->performance_nbr_goals;
    }

    public function setPerformance_nbr_goals($value)
    {
        $this->performance_nbr_goals = $value;
    }

    public function getPerformance_assists()
    {
        return $this->performance_assists;
    }

    public function setPerformance_assists($value)
    {
        $this->performance_assists = $value;
    }

    public function getPerformance_date_recorded()
    {
        return $this->performance_date_recorded;
    }

    public function setPerformance_date_recorded($value)
    {
        $this->performance_date_recorded = $value;
    }

    public function getPerformance_nbr_fouls()
    {
        return $this->performance_nbr_fouls;
    }

    public function setPerformance_nbr_fouls($value)
    {
        $this->performance_nbr_fouls = $value;
    }
}