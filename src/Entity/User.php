<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: "user")]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(name: "user_id", type: "integer")]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: "user_fname", type: "string", length: 255)]
    private string $user_fname;

    #[ORM\Column(name: "user_lname", type: "string", length: 255)]
    private string $user_lname;

    #[ORM\Column(name: "user_email", type: "string", length: 255)]
    private string $user_email;

    #[ORM\Column(name: "user_pwd", type: "string", length: 255)]
    private string $user_pwd;

    #[ORM\Column(name: "user_nbr", type: "string", length: 255)]
    private string $user_nbr;

    #[ORM\Column(name: "user_role", type: "string", length: 50)]
    private string $user_role;

    #[ORM\Column(name: "nb_teams", type: "integer", nullable: true)]
    private ?int $nb_teams;

    #[ORM\Column(name: "med_specialty", type: "string", length: 255, nullable: true)]
    private ?string $med_specialty = null;

    #[ORM\Column(name: "athlete_DoB", type: "date", nullable: true)]
    private ?\DateTimeInterface $athlete_DoB = null;

    #[ORM\Column(name: "athlete_gender", type: "string", length: 50, nullable: true)]
    private ?string $athlete_gender = null;

    #[ORM\Column(name: "athlete_address", type: "string", length: 255, nullable: true)]
    private ?string $athlete_address = null;

    #[ORM\Column(name: "athlete_height", type: "float", nullable: true)]
    private ?float $athlete_height = null;

    #[ORM\Column(name: "athlete_weight", type: "float", nullable: true)]
    private ?float $athlete_weight = null;

    #[ORM\Column(name: "isInjured", type: "boolean", nullable: true)]
    private ?bool $isInjured = false;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: "users")]
    #[ORM\JoinColumn(
        name: "athlete_teamId",
        referencedColumnName: "teamId",
        nullable: true,
        onDelete: "CASCADE",
    )]
    private ?Team $team = null;

    #[ORM\OneToMany(
        mappedBy: "user",
        targetEntity: Injury::class,
        orphanRemoval: true
    )]
    private Collection $injuries;

    public function __construct()
    {
        $this->injuries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserFname(): string
    {
        return $this->user_fname;
    }

    public function setUserFname(string $user_fname): self
    {
        $this->user_fname = $user_fname;
        return $this;
    }

    public function getUserLname(): string
    {
        return $this->user_lname;
    }

    public function setUserLname(string $user_lname): self
    {
        $this->user_lname = $user_lname;
        return $this;
    }

    public function getUserEmail(): string
    {
        return $this->user_email;
    }

    public function setUserEmail(string $user_email): self
    {
        $this->user_email = $user_email;
        return $this;
    }

    public function getUserPwd(): string
    {
        return $this->user_pwd;
    }

    public function setUserPwd(string $user_pwd): self
    {
        $this->user_pwd = $user_pwd;
        return $this;
    }

    public function getUserNbr(): string
    {
        return $this->user_nbr;
    }

    public function setUserNbr(string $user_nbr): self
    {
        $this->user_nbr = $user_nbr;
        return $this;
    }

    public function getUserRole(): string
    {
        return $this->user_role;
    }

    public function setUserRole(string $user_role): self
    {
        $this->user_role = $user_role;
        return $this;
    }

    public function getNbTeams(): ?int
    {
        return $this->nb_teams;
    }

    public function setNbTeams(?int $nb_teams): self
    {
        $this->nb_teams = $nb_teams;
        return $this;
    }

    public function getMedSpecialty(): ?string
    {
        return $this->med_specialty;
    }

    public function setMedSpecialty(?string $med_specialty): self
    {
        $this->med_specialty = $med_specialty;
        return $this;
    }

    public function getAthleteDoB(): ?\DateTimeInterface
    {
        return $this->athlete_DoB;
    }

    public function setAthleteDoB(?\DateTimeInterface $athlete_DoB): self
    {
        $this->athlete_DoB = $athlete_DoB;
        return $this;
    }

    public function getAthleteGender(): ?string
    {
        return $this->athlete_gender;
    }

    public function setAthleteGender(?string $athlete_gender): self
    {
        $this->athlete_gender = $athlete_gender;
        return $this;
    }

    public function getAthleteAddress(): ?string
    {
        return $this->athlete_address;
    }

    public function setAthleteAddress(?string $athlete_address): self
    {
        $this->athlete_address = $athlete_address;
        return $this;
    }

    public function getAthleteHeight(): ?float
    {
        return $this->athlete_height;
    }

    public function setAthleteHeight(?float $athlete_height): self
    {
        $this->athlete_height = $athlete_height;
        return $this;
    }

    public function getAthleteWeight(): ?float
    {
        return $this->athlete_weight;
    }

    public function setAthleteWeight(?float $athlete_weight): self
    {
        $this->athlete_weight = $athlete_weight;
        return $this;
    }

    public function getIsInjured(): ?bool
    {
        return $this->isInjured;
    }

    public function setIsInjured(?bool $isInjured): self
    {
        $this->isInjured = $isInjured;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }
    // Required by UserInterface
    public function getRoles(): array
    {
        // Map your user_role to Symfony roles
        $role = strtoupper($this->user_role);
        return ["ROLE_" . $role];
    }

    public function getPassword(): string
    {
        return $this->user_pwd;
    }

    public function getUserIdentifier(): string
    {
        return $this->user_email; // Used for login (email in your case)
    }

    public function eraseCredentials(): void
    {
        // If you store temporary sensitive data, clear it here
    }

    public function getInjuries(): Collection
    {
        return $this->injuries;
    }

    public function addInjury(Injury $injury): self
    {
        if (!$this->injuries->contains($injury)) {
            $this->injuries[] = $injury;
            $injury->setUser($this);
        }
        return $this;
    }

    public function removeInjury(Injury $injury): self
    {
        $this->injuries->removeElement($injury);
        return $this;
    }
}