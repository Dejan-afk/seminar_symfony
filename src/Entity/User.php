<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[ORM\JoinColumn(nullable: false)]
    private string $email;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: false)]
    private string $password;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'organizer', targetEntity: Seminar::class)]
    private Collection $organizedSeminars;

    public function __construct()
    {
        $this->organizedSeminars = new ArrayCollection();
    }

    public function addOrganizedSeminar(Seminar $seminar): static
    {
        if (!$this->organizedSeminars->contains($seminar)) {
            $this->organizedSeminars->add($seminar);
            $seminar->setOrganizer($this);
        }
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function eraseCredentials(): void
    {
    }
}