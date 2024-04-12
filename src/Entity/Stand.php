<?php

namespace App\Entity;

use App\Repository\StandRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StandRepository::class)]
class Stand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getStands"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ nom est obligatoire")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne doit pas faire plus de {{ limit }} caractères ")]   #[Groups(["getStands"])]
    private ?string $name = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getStands"])]
    private bool $is_competitive = false;

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

}
