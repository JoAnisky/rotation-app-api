<?php

namespace App\Entity;

use App\Repository\ScenarioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

enum Status: string
{
    case NOT_STARTED = 'NOT_STARTED';
    case ROTATING = 'ROTATING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case PAUSED = 'PAUSED';
    case COMPLETED = 'COMPLETED';
}

#[ORM\Entity(repositoryClass: ScenarioRepository::class)]
class Scenario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getScenario"])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getScenario"])]
    private ?array $base_scenario = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getScenario"])]
    private ?array $current_scenario = null;

    #[ORM\OneToOne(inversedBy: 'scenario', cascade: ['persist', 'remove'])]
    #[Groups(["getScenario"])]
    private ?Activity $activity = null;

    #[ORM\Column(type: 'string', enumType: Status::class)]
    #[Groups(["getScenario"])]
    private Status $status = Status::NOT_STARTED;

    public function __construct()
    {
        $this->status = Status::NOT_STARTED; // Default status
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseScenario(): ?array
    {
        return $this->base_scenario;
    }

    public function setBaseScenario(?array $base_scenario): static
    {
        $this->base_scenario = $base_scenario;

        return $this;
    }

    public function getCurrentScenario(): ?array
    {
        return $this->current_scenario;
    }

    public function setCurrentScenario(?array $current_scenario): static
    {
        $this->current_scenario = $current_scenario;

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): static
    {
        $this->activity = $activity;

        return $this;
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
}
