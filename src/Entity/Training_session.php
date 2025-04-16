<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Training_session
{
    #[ORM\Id]
    #[ORM\Column(name: "trainingSession_id", type: "integer")]
    private int $trainingSession_id;

    #[ORM\Column(name: "session_focus", type: "string")]
    private string $session_focus;

    #[ORM\Column(name: "session_start_time", type: "string")]
    private string $session_start_time;

    #[ORM\Column(name: "session_duration", type: "string")]
    private string $session_duration;

    #[ORM\Column(name: "session_location", type: "string", length: 25)]
    private string $session_location;

    #[ORM\Column(name: "session_notes", type: "string", length: 250)]
    private string $session_notes;

    public function getTrainingSession_id()
    {
        return $this->trainingSession_id;
    }

    public function setTrainingSession_id($value)
    {
        $this->trainingSession_id = $value;
    }

    public function getSession_focus()
    {
        return $this->session_focus;
    }

    public function setSession_focus($value)
    {
        $this->session_focus = $value;
    }

    public function getSession_start_time()
    {
        return $this->session_start_time;
    }

    public function setSession_start_time($value)
    {
        $this->session_start_time = $value;
    }

    public function getSession_duration()
    {
        return $this->session_duration;
    }

    public function setSession_duration($value)
    {
        $this->session_duration = $value;
    }

    public function getSession_location()
    {
        return $this->session_location;
    }

    public function setSession_location($value)
    {
        $this->session_location = $value;
    }

    public function getSession_notes()
    {
        return $this->session_notes;
    }

    public function setSession_notes($value)
    {
        $this->session_notes = $value;
    }
}