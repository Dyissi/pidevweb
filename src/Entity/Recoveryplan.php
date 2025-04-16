<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Recoveryplan
{
    #[ORM\Id]
    #[ORM\Column(name: "recovery_id", type: "integer")]
    private int $recovery_id;

    #[ORM\Column(name: "user_id", type: "integer")]
    private int $user_id;

    #[ORM\Column(name: "injury_id", type: "integer")]
    private int $injury_id;

    #[ORM\Column(name: "recovery_Goal", type: "string")]
    private string $recovery_Goal;

    #[ORM\Column(name: "recovery_Description", type: "text")]
    private string $recovery_Description;

    #[ORM\Column(name: "recovery_StartDate", type: "date")]
    private \DateTimeInterface $recovery_StartDate;

    #[ORM\Column(name: "recovery_EndDate", type: "date")]
    private \DateTimeInterface $recovery_EndDate;

    #[ORM\Column(name: "Recovery_Status", type: "string")]
    private string $Recovery_Status;

    public function getRecovery_id()
    {
        return $this->recovery_id;
    }

    public function setRecovery_id($value)
    {
        $this->recovery_id = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getInjury_id()
    {
        return $this->injury_id;
    }

    public function setInjury_id($value)
    {
        $this->injury_id = $value;
    }

    public function getRecovery_Goal()
    {
        return $this->recovery_Goal;
    }

    public function setRecovery_Goal($value)
    {
        $this->recovery_Goal = $value;
    }

    public function getRecovery_Description()
    {
        return $this->recovery_Description;
    }

    public function setRecovery_Description($value)
    {
        $this->recovery_Description = $value;
    }

    public function getRecovery_StartDate()
    {
        return $this->recovery_StartDate;
    }

    public function setRecovery_StartDate($value)
    {
        $this->recovery_StartDate = $value;
    }

    public function getRecovery_EndDate()
    {
        return $this->recovery_EndDate;
    }

    public function setRecovery_EndDate($value)
    {
        $this->recovery_EndDate = $value;
    }

    public function getRecovery_Status()
    {
        return $this->Recovery_Status;
    }

    public function setRecovery_Status($value)
    {
        $this->Recovery_Status = $value;
    }
}