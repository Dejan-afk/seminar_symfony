<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SeminarCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 150)]
    public string $title = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 20, max: 5000)]
    public string $description = '';

    #[Assert\NotNull]
    public ?\DateTimeImmutable $startDate = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $endDate = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $registrationDeadline = null;

    #[Assert\Positive]
    public int $maxParticipants = 10;

    /**
     * @var SessionInputDto[]
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'Mindestens eine Session ist erforderlich.')]
    public array $sessions = [];
}