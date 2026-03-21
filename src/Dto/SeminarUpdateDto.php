<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SeminarUpdateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    public ?string $description = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $startDate = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $endDate = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $registrationDeadline = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $maxParticipants = null;

    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $sessions = [];

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->startDate && $this->endDate && $this->startDate >= $this->endDate) {
            $context->buildViolation('Das Startdatum muss vor dem Enddatum liegen.')
                ->atPath('startDate')
                ->addViolation();
        }

        if ($this->registrationDeadline && $this->startDate && $this->registrationDeadline > $this->startDate) {
            $context->buildViolation('Die Anmeldefrist muss vor dem Startdatum liegen.')
                ->atPath('registrationDeadline')
                ->addViolation();
        }

        if ($this->startDate && $this->endDate) {
            foreach ($this->sessions as $index => $session) {
                if (!$session instanceof SessionInputDto) {
                    continue;
                }

                if ($session->startsAt && $session->startsAt < $this->startDate) {
                    $context->buildViolation('Die Session darf nicht vor dem Seminarbeginn starten.')
                        ->atPath(sprintf('sessions[%d].startsAt', $index))
                        ->addViolation();
                }

                if ($session->endsAt && $session->endsAt > $this->endDate) {
                    $context->buildViolation('Die Session darf nicht nach dem Seminarende enden.')
                        ->atPath(sprintf('sessions[%d].endsAt', $index))
                        ->addViolation();
                }
            }
        }
    }
}