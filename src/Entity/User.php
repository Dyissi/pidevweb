<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Claim;

#[ORM\Entity]
class User
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $user_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $user_fname;

    #[ORM\Column(type: "string", length: 255)]
    private string $user_lname;

    #[ORM\Column(type: "string", length: 255)]
    private string $user_email;

    #[ORM\Column(type: "string", length: 255)]
    private string $user_pwd;

    #[ORM\Column(type: "string", length: 255)]
    private string $user_nbr;

    #[ORM\Column(type: "string", length: 20)]
    private string $user_role;

    #[ORM\Column(type: "integer")]
    private int $nb_teams;

    #[ORM\Column(type: "string", length: 255)]
    private string $med_specialty;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $athlete_DoB;

    #[ORM\Column(type: "string", length: 255)]
    private string $athlete_gender;

    #[ORM\Column(type: "string", length: 255)]
    private string $athlete_address;

    #[ORM\Column(type: "float")]
    private float $athlete_height;

    #[ORM\Column(type: "float")]
    private float $athlete_weight;

    #[ORM\Column(type: "boolean")]
    private bool $isInjured;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $athlete_regDate;

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getUser_fname()
    {
        return $this->user_fname;
    }

    public function setUser_fname($value)
    {
        $this->user_fname = $value;
    }

    public function getUser_lname()
    {
        return $this->user_lname;
    }

    public function setUser_lname($value)
    {
        $this->user_lname = $value;
    }

    public function getUser_email()
    {
        return $this->user_email;
    }

    public function setUser_email($value)
    {
        $this->user_email = $value;
    }

    public function getUser_pwd()
    {
        return $this->user_pwd;
    }

    public function setUser_pwd($value)
    {
        $this->user_pwd = $value;
    }

    public function getUser_nbr()
    {
        return $this->user_nbr;
    }

    public function setUser_nbr($value)
    {
        $this->user_nbr = $value;
    }

    public function getUser_role()
    {
        return $this->user_role;
    }

    public function setUser_role($value)
    {
        $this->user_role = $value;
    }

    public function getNb_teams()
    {
        return $this->nb_teams;
    }

    public function setNb_teams($value)
    {
        $this->nb_teams = $value;
    }

    public function getMed_specialty()
    {
        return $this->med_specialty;
    }

    public function setMed_specialty($value)
    {
        $this->med_specialty = $value;
    }

    public function getAthlete_DoB()
    {
        return $this->athlete_DoB;
    }

    public function setAthlete_DoB($value)
    {
        $this->athlete_DoB = $value;
    }

    public function getAthlete_gender()
    {
        return $this->athlete_gender;
    }

    public function setAthlete_gender($value)
    {
        $this->athlete_gender = $value;
    }

    public function getAthlete_address()
    {
        return $this->athlete_address;
    }

    public function setAthlete_address($value)
    {
        $this->athlete_address = $value;
    }

    public function getAthlete_height()
    {
        return $this->athlete_height;
    }

    public function setAthlete_height($value)
    {
        $this->athlete_height = $value;
    }

    public function getAthlete_weight()
    {
        return $this->athlete_weight;
    }

    public function setAthlete_weight($value)
    {
        $this->athlete_weight = $value;
    }

    public function getIsInjured()
    {
        return $this->isInjured;
    }

    public function setIsInjured($value)
    {
        $this->isInjured = $value;
    }

    public function getAthlete_regDate()
    {
        return $this->athlete_regDate;
    }

    public function setAthlete_regDate($value)
    {
        $this->athlete_regDate = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Claim::class)]
    private Collection $submittedClaims;

        public function getSubmittedClaims(): Collection
        {
            return $this->submittedClaims;
        }
    
        public function addSubmittedClaim(Claim $claim): self
        {
            if (!$this->submittedClaims->contains($claim)) {
                $this->submittedClaims[] = $claim;
                $claim->setIdUser($this);
            }
    
            return $this;
        }
    
        public function removeSubmittedClaim(Claim $claim): self
        {
            if ($this->submittedClaims->removeElement($claim)) {
                // set the owning side to null (unless already changed)
                if ($claim->getIdUser() === $this) {
                    $claim->setIdUser(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "id_user_to_claim", targetEntity: Claim::class)]
    private Collection $receivedClaims;

        public function getReceivedClaims(): Collection
        {
            return $this->receivedClaims;
        }
    
        public function addReceivedClaim(Claim $claim): self
        {
            if (!$this->receivedClaims->contains($claim)) {
                $this->receivedClaims[] = $claim;
                $claim->setIdUserToClaim($this);
            }
    
            return $this;
        }
    
        public function removeReceivedClaim(Claim $claim): self
        {
            if ($this->receivedClaims->removeElement($claim)) {
                // set the owning side to null (unless already changed)
                if ($claim->getIdUserToClaim() === $this) {
                    $claim->setIdUserToClaim(null);
                }
            }
    
            return $this;
        }

        public function __construct()
    {
        $this->submittedClaims = new ArrayCollection();
        $this->receivedClaims = new ArrayCollection();
    }
}
