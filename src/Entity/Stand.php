<?php

namespace App\Entity;

use App\Repository\StandRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
#[ORM\Entity(repositoryClass: StandRepository::class)]
class Stand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getStands"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getStands"])]
    private ?string $name = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getStands"])]
    private bool $is_competitive = false;

    #[ORM\OneToOne(inversedBy: 'stand', cascade: ['persist', 'remove'])]
    #[Groups(["getStands"])]
    private ?Animator $animator = null;

    #[ORM\ManyToOne(inversedBy: 'stand', cascade: ['persist'])]
    #[Groups(["getStands"])]
    private ?Activity $activity = null;

    #[ORM\ManyToOne(inversedBy: 'stands')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

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

    public function getIsCompetitive(): ?bool
    {
        return $this->is_competitive;
    }

    public function setIsCompetitive(bool $is_competitive): static
    {
        $this->is_competitive = $is_competitive;

        return $this;
    }

    public function getAnimator(): ?Animator
    {
        return $this->animator;
    }

    public function setAnimator(?Animator $animator): static
    {
        $this->animator = $animator;

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
