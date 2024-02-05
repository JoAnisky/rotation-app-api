<?php

namespace App\Entity;

use App\Repository\AnimatorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimatorRepository::class)]
class Animator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(mappedBy: 'animator_id', cascade: ['persist', 'remove'])]
    private ?Stand $stand = null;

    #[ORM\ManyToOne(inversedBy: 'animators')]
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

    public function getStand(): ?Stand
    {
        return $this->stand;
    }

    public function setStand(?Stand $stand): static
    {
        // unset the owning side of the relation if necessary
        if ($stand === null && $this->stand !== null) {
            $this->stand->setAnimator(null);
        }

        // set the owning side of the relation if necessary
        if ($stand !== null && $stand->getAnimator() !== $this) {
            $stand->setAnimator($this);
        }

        $this->stand = $stand;

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
