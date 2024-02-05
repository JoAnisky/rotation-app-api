<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?\DateTimeInterface $global_duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $rotation_duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $stand_duration = null;


    #[ORM\OneToMany(mappedBy: 'activity_id', targetEntity: Team::class)]
    private Collection $team;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Stand::class)]
    private Collection $stand;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->team = new ArrayCollection();
        $this->stand = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Team>
     */
    public function getTeam(): Collection
    {
        return $this->team;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->team->contains($team)) {
            $this->team->add($team);
            $team->setActivity($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->team->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getActivity() === $this) {
                $team->setActivity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Stand>
     */
    public function getStand(): Collection
    {
        return $this->stand;
    }

    public function addStand(Stand $stand): static
    {
        if (!$this->stand->contains($stand)) {
            $this->stand->add($stand);
            $stand->setActivity($this);
        }

        return $this;
    }

    public function removeStand(Stand $stand): static
    {
        if ($this->stand->removeElement($stand)) {
            // set the owning side to null (unless already changed)
            if ($stand->getActivity() === $this) {
                $stand->setActivity(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
