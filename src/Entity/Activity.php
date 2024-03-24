<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

enum Status: string
{
    case NOT_STARTED = 'NOT_STARTED';
    case ROTATING = 'ROTATING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case PAUSED = 'PAUSED';
    case COMPLETED = 'COMPLETED';
}

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[HasLifecycleCallbacks]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getStands", "getAnimators", "getTeams"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getActivity"])]
    #[Assert\NotBlank(message: "Le champ nom est obligatoire")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne doit pas faire plus de {{ limit }} caractères ")]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getActivity"])]
    private ?\DateTimeImmutable $activity_date = null;

    #[ORM\Column]
    #[Groups(["getActivity"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'string', enumType: Status::class)]
    #[Groups(["getActivity"])]
    private Status $status = Status::NOT_STARTED;

    #[ORM\Column(nullable: true)]
    #[Groups(["getActivity"])]
    private ?int $nb_participants = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getActivity"])]
    private ?int $nb_teams = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(["getActivity"])]
    private ?int $global_duration = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(["getActivity"])]
    private ?\DateTimeInterface $rotation_duration = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(["getActivity"])]
    private ?\DateTimeInterface $stand_duration = null;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Team::class)]
    #[Groups(["getActivity"])]
    private Collection $team;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Stand::class)]
    #[Groups(["getActivity"])]
    private Collection $stand;

    #[ORM\ManyToOne(inversedBy: 'activity')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(["getActivity"])]
    private ?string $activity_start_time = null;

    public function __construct()
    {
        $this->team = new ArrayCollection();
        $this->stand = new ArrayCollection();
        $this->status = Status::NOT_STARTED; // Default status
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

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;

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

    public function getGlobalDuration(): ?int
    {
        return $this->global_duration;
    }

    public function setGlobalDuration(?int $global_duration): static
    {
        $this->global_duration = $global_duration;

        return $this;
    }

    public function getRotationDuration(): ?int
    {
        return $this->rotation_duration;
    }

    public function setRotationDuration(?int $rotation_duration): static
    {
        $this->rotation_duration = $rotation_duration;

        return $this;
    }

    public function getStandDuration(): ?int
    {
        return $this->stand_duration;
    }

    public function setStandDuration(?int $stand_duration): static
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

    public function getActivityStartTime(): ?string
    {
        return $this->activity_start_time;
    }

    public function setActivityStartTime(?string $activity_start_time): static
    {
        $this->activity_start_time = $activity_start_time;

        return $this;
    }

}
