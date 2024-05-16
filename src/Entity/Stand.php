<?php

namespace App\Entity;

use App\Repository\StandRepository;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 1, max: 50, notInRangeMessage: "Le nombre d'équipes doit être entre 1 et 50.")]
    #[Groups(["getStands"])]
    private ?int $nbTeamsOnStand = 1;

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

    public function getNbTeamsOnStand(): ?int
    {
        return $this->nbTeamsOnStand;
    }

    public function setNbTeamsOnStand(int $nbTeamsOnStand): static
    {
        $this->nbTeamsOnStand = $nbTeamsOnStand;

        return $this;
    }

}
