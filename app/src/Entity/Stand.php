<?php

namespace App\Entity;

use App\Repository\StandRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StandRepository::class)]
class Stand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $is_competitive = null;

    #[ORM\OneToOne(mappedBy: 'stand', cascade: ['persist', 'remove'])]
    private ?StandParticipation $standParticipation = null;

    #[ORM\OneToOne(inversedBy: 'stand', cascade: ['persist', 'remove'])]
    private ?Animator $animator_id = null;

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

    public function isIsCompetitive(): ?bool
    {
        return $this->is_competitive;
    }

    public function setIsCompetitive(bool $is_competitive): static
    {
        $this->is_competitive = $is_competitive;

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
            $this->standParticipation->setStand(null);
        }

        // set the owning side of the relation if necessary
        if ($standParticipation !== null && $standParticipation->getStand() !== $this) {
            $standParticipation->setStand($this);
        }

        $this->standParticipation = $standParticipation;

        return $this;
    }

    public function getAnimatorId(): ?Animator
    {
        return $this->animator_id;
    }

    public function setAnimatorId(?Animator $animator_id): static
    {
        $this->animator_id = $animator_id;

        return $this;
    }
}
