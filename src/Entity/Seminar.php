<?php

namespace App\Entity;

use App\Repository\SeminarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeminarRepository::class)]
class Seminar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $endDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $registrationDeadline;

    #[ORM\Column]
    private int $maxParticipants = 10;

    #[ORM\ManyToOne(inversedBy: 'organizedSeminars')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[ORM\OneToMany(mappedBy: 'seminar', targetEntity: Session::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $sessions;

    #[ORM\OneToMany(mappedBy: 'seminar', targetEntity: Registration::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $registrations;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->registrations = new ArrayCollection();
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setSeminar($this);
        }
        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            if ($session->getSeminar() === $this) {
                $session->setSeminar(null);
            }
        }
        return $this;
    }

    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addRegistration(Registration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setSeminar($this);
        }
        return $this;
    }

    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function getRegistrationCount(): int
    {
        return $this->registrations->count();
    }

    public function isFull(): bool
    {
        return $this->getRegistrationCount() >= $this->maxParticipants;
    }

    public function removeRegistration(Registration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            if ($registration->getSeminar() === $this) {
                $registration->setSeminar(null);
            }
        }
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganizer(): User
    {
        return $this->organizer;
    }

    public function setOrganizer(User $organizer): static
    {
        $this->organizer = $organizer;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getRegistrationDeadline(): \DateTimeImmutable
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(\DateTimeImmutable $registrationDeadline): void
    {
        $this->registrationDeadline = $registrationDeadline;
    }

    public function getMaxParticipants(): int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): void
    {
        $this->maxParticipants = $maxParticipants;
    }
}