<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SessionInputDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 120)]
    public string $title = '';

    #[Assert\NotNull]
    public ?\DateTimeImmutable $startsAt = null;

    #[Assert\NotNull]
    public ?\DateTimeImmutable $endsAt = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->startsAt && $this->endsAt && $this->startsAt >= $this->endsAt) {
            $context->buildViolation('Das Ende der Session muss nach dem Start liegen.')
                ->atPath('endsAt')
                ->addViolation();
        }
    }
}