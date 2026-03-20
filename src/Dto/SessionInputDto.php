<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SessionInputDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 120)]
    public string $title = '';

    #[Assert\NotNull]
    public ?\DateTimeImmutable $startsAt = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $endsAt = null;
}