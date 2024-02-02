<?php

namespace App\Entity;

use App\Repository\StandParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StandParticipationRepository::class)]
class StandParticipation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'standParticipation', cascade: ['persist', 'remove'])]
    private ?Stand $stand = null;

    #[ORM\OneToOne(inversedBy: 'standParticipation', cascade: ['persist', 'remove'])]
    private ?Activity $activity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStand(): ?Stand
    {
        return $this->stand;
    }

    public function setStand(?Stand $stand): static
    {
        $this->stand = $stand;

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
}
