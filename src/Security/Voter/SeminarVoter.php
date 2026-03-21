<?php

namespace App\Security\Voter;

use App\Entity\Seminar;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SeminarVoter extends Voter
{
    public const EDIT = 'SEMINAR_EDIT';
    public const DELETE = 'SEMINAR_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE], true)
            && $subject instanceof Seminar;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Seminar $seminar */
        $seminar = $subject;

        return match ($attribute) {
            self::DELETE, self::EDIT => $seminar->getOrganizer()?->getId() === $user->getId(),
            default => false,
        };
    }
}