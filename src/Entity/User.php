<?php

namespace App\Entity;

use App\Entity\Animator;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_GAMEMASTER = 'ROLE_GAMEMASTER';
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers","getAnimators"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ login est obligatoire")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le login doit faire au moins {{ limit }} caractères", maxMessage: "Le login ne doit pas faire plus de {{ limit }} caractères ")]
    #[Groups(["getUsers"])]
    private ?string $login = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ password est obligatoire")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le password doit faire au moins {{ limit }} caractères", maxMessage: "Le password ne doit pas faire plus de {{ limit }} caractères ")]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(["getUsers"])]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Animator::class)]
    private Collection $animators;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Team::class)]
    private Collection $teams;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Activity::class)]
    #[Groups(["getActivity"])]
    private Collection $activities;

    public function __construct()
    {
        $this->animators = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->activities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, Animator>
     */
    public function getUsers(): Collection
    {
        return $this->animators;
    }

    public function addAnimator(Animator $animator): static
    {
        if (!$this->animators->contains($animator)) {
            $this->animators->add($animator);
            $animator->setUser($this);
        }

        return $this;
    }

    public function removeAnimator(Animator $animator): static
    {
        if ($this->animators->removeElement($animator)) {
            // set the owning side to null (unless already changed)
            if ($animator->getUser() === $this) {
                $animator->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->setUser($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->teams->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getUser() === $this) {
                $team->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setUser($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getUser() === $this) {
                $activity->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Méthode getUsername qui permet de retourner le champ qui est utilisé pour l'authentification.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }
}
