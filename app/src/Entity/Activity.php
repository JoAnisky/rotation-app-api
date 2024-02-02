<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $activity_date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private array $statut = [];

    #[ORM\Column(nullable: true)]
    private ?int $nb_participants = null;

    #[ORM\Column(nullable: true)]
    private ?int $nb_teams = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $global_duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $rotation_duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $stand_duration = null;

    #[ORM\OneToOne(mappedBy: 'activity', cascade: ['persist', 'remove'])]
    private ?TeamParticipation $teamParticipation = null;

    #[ORM\OneToOne(mappedBy: 'activity', cascade: ['persist', 'remove'])]
    private ?StandParticipation $standParticipation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getActivityDate(): ?\DateTimeImmutable
    {
        return $this->activity_date;
    }

    public function setActivityDate(?\DateTimeImmutable $activity_date): static
    {
        $this->activity_date = $activity_date;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatut(): array
    {
        return $this->statut;
    }

    public function setStatut(array $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getNbParticipants(): ?int
    {
        return $this->nb_participants;
    }

    public function setNbParticipants(?int $nb_participants): static
    {
        $this->nb_participants = $nb_participants;

        return $this;
    }

    public function getNbTeams(): ?int
    {
        return $this->nb_teams;
    }

    public function setNbTeams(?int $nb_teams): static
    {
        $this->nb_teams = $nb_teams;

        return $this;
    }

    public function getGlobalDuration(): ?\DateTimeInterface
    {
        return $this->global_duration;
    }

    public function setGlobalDuration(?\DateTimeInterface $global_duration): static
    {
        $this->global_duration = $global_duration;

        return $this;
    }

    public function getRotationDuration(): ?\DateTimeInterface
    {
        return $this->rotation_duration;
    }

    public function setRotationDuration(?\DateTimeInterface $rotation_duration): static
    {
        $this->rotation_duration = $rotation_duration;

        return $this;
    }

    public function getStandDuration(): ?\DateTimeInterface
    {
        return $this->stand_duration;
    }

    public function setStandDuration(?\DateTimeInterface $stand_duration): static
    {
        $this->stand_duration = $stand_duration;

        return $this;
    }

    public function getTeamParticipation(): ?TeamParticipation
    {
        return $this->teamParticipation;
    }

    public function setTeamParticipation(?TeamParticipation $teamParticipation): static
    {
        // unset the owning side of the relation if necessary
        if ($teamParticipation === null && $this->teamParticipation !== null) {
            $this->teamParticipation->setActivity(null);
        }

        // set the owning side of the relation if necessary
        if ($teamParticipation !== null && $teamParticipation->getActivity() !== $this) {
            $teamParticipation->setActivity($this);
        }

        $this->teamParticipation = $teamParticipation;

        return $this;
    }

    public function getStandParticipation(): ?StandParticipation
    {
        return $this->standParticipation;
    }

    public function setStandParticipation(?StandParticipation $standParticipation): static
    {
        // unset the owning side of the relation if necessary
        if ($standParticipation === null && $this->standParticipation !== null) {
            $this->standParticipation->setActivity(null);
        }

        // set the owning side of the relation if necessary
        if ($standParticipation !== null && $standParticipation->getActivity() !== $this) {
            $standParticipation->setActivity($this);
        }

        $this->standParticipation = $standParticipation;

        return $this;
    }
}
