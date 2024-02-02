<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(mappedBy: 'team', cascade: ['persist', 'remove'])]
    private ?TeamParticipation $teamParticipation = null;

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

    public function getTeamParticipation(): ?TeamParticipation
    {
        return $this->teamParticipation;
    }

    public function setTeamParticipation(?TeamParticipation $teamParticipation): static
    {
        // unset the owning side of the relation if necessary
        if ($teamParticipation === null && $this->teamParticipation !== null) {
            $this->teamParticipation->setTeam(null);
        }

        // set the owning side of the relation if necessary
        if ($teamParticipation !== null && $teamParticipation->getTeam() !== $this) {
            $teamParticipation->setTeam($this);
        }

        $this->teamParticipation = $teamParticipation;

        return $this;
    }
}
