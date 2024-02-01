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

    #[ORM\Column(type: Types::ARRAY)]
    private array $statut = [];

    #[ORM\Column(nullable: true)]
    private ?int $nb_participants = null;

    #[ORM\Column(nullable: true)]
    private ?int $nb_teams = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $global_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $rotation_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $stand_time = null;

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

    public function getGlobalTime(): ?\DateTimeInterface
    {
        return $this->global_time;
    }

    public function setGlobalTime(?\DateTimeInterface $global_time): static
    {
        $this->global_time = $global_time;

        return $this;
    }

    public function getRotationTime(): ?\DateTimeInterface
    {
        return $this->rotation_time;
    }

    public function setRotationTime(?\DateTimeInterface $rotation_time): static
    {
        $this->rotation_time = $rotation_time;

        return $this;
    }

    public function getStandTime(): ?\DateTimeInterface
    {
        return $this->stand_time;
    }

    public function setStandTime(?\DateTimeInterface $stand_time): static
    {
        $this->stand_time = $stand_time;

        return $this;
    }
}
