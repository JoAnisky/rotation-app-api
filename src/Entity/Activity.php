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
    #[Groups(["getActivity", "getStands", "getAnimators", "getTeams", "getStopwatch"])]
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
    private ?int $rotation_duration = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(["getActivity"])]
    private ?int $stand_duration = null;

    #[ORM\OneToMany(mappedBy: 'activity', targetEntity: Team::class)]
    #[Groups(["getActivity"])]
    private Collection $team;

    #[ORM\ManyToOne(inversedBy: 'activity')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(["getActivity"])]
    private ?string $activity_start_time = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $pause_start_time = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $pause_duration = null;

    #[ORM\OneToOne(mappedBy: 'activity', cascade: ['persist', 'remove'])]
    private ?Stopwatch $stopwatch = null;

    #[ORM\OneToOne(mappedBy: 'activity', cascade: ['persist', 'remove'])]
    private ?Scenario $scenario = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getActivity"])]
    private ?array $stands = null;

    public function __construct()
    {
        $this->team = new ArrayCollection();
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

    public function getPauseStartTime(): ?string
    {
        return $this->pause_start_time;
    }

    public function setPauseStartTime(?string $pause_start_time): static
    {
        $this->pause_start_time = $pause_start_time;

        return $this;
    }

    public function getPauseDuration(): ?string
    {
        return $this->pause_duration;
    }

    public function setPauseDuration(?string $pause_duration): static
    {
        $this->pause_duration = $pause_duration;

        return $this;
    }

    public function getStopwatch(): ?Stopwatch
    {
        return $this->stopwatch;
    }

    public function setStopwatch(?Stopwatch $stopwatch): static
    {
        // unset the owning side of the relation if necessary
        if ($stopwatch === null && $this->stopwatch !== null) {
            $this->stopwatch->setActivity(null);
        }

        // set the owning side of the relation if necessary
        if ($stopwatch !== null && $stopwatch->getActivity() !== $this) {
            $stopwatch->setActivity($this);
        }

        $this->stopwatch = $stopwatch;

        return $this;
    }

    public function getScenario(): ?Scenario
    {
        return $this->scenario;
    }

    public function setScenario(?Scenario $scenario): static
    {
        // unset the owning side of the relation if necessary
        if ($scenario === null && $this->scenario !== null) {
            $this->scenario->setActivity(null);
        }

        // set the owning side of the relation if necessary
        if ($scenario !== null && $scenario->getActivity() !== $this) {
            $scenario->setActivity($this);
        }

        $this->scenario = $scenario;

        return $this;
    }

    public function getStands(): ?array
    {
        return $this->stands;
    }

    public function setStands(?array $stands): static
    {
        $this->stands = $stands;

        return $this;
    }

}
