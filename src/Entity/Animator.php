<?php

namespace App\Entity;

use App\Repository\AnimatorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnimatorRepository::class)]
class Animator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAnimators"])]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne doit pas faire plus de {{ limit }} caractères ")]
    #[ORM\Column(length: 255)]
    #[Groups(["getAnimators"])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getAnimators"])]
    private ?array $stands = null;

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

    public function getStands(): ?array
    {
        return $this->stands;
    }

    public function setStands(?array $stands): static
    {
        $this->stands = $stands;

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
