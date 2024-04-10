<?php

namespace App\Entity;

use App\Repository\StopwatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StopwatchRepository::class)]
class Stopwatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getStopwatch"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(["getStopwatch"])]
    private ?string $duration = null;

    #[ORM\OneToOne(inversedBy: 'stopwatch', cascade: ['persist', 'remove'])]
    #[Groups(["getStopwatch"])]
    private ?Activity $activity = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $counter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): static
    {
        $this->duration = $duration;

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

    public function getCounter(): ?string
    {
        return $this->counter;
    }

    public function setCounter(?string $counter): static
    {
        $this->counter = $counter;

        return $this;
    }
}
