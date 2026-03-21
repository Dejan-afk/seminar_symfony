<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Seminar;
use App\Entity\User;
use App\Security\Voter\SeminarVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

class SeminarVoterTest extends TestCase
{
    private SeminarVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new SeminarVoter();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testOrganizerCanEditOwnSeminar(): void
    {
        $organizer = $this->createUser(1, 'organizer@example.com');
        $seminar = $this->createSeminar($organizer);

        $token = $this->createTokenMock($organizer);

        self::assertTrue(
            $this->voter->vote($token, $seminar, [SeminarVoter::EDIT]) > 0
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testOrganizerCanDeleteOwnSeminar(): void
    {
        $organizer = $this->createUser(1, 'organizer@example.com');
        $seminar = $this->createSeminar($organizer);

        $token = $this->createTokenMock($organizer);

        self::assertTrue(
            $this->voter->vote($token, $seminar, [SeminarVoter::DELETE]) > 0
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDifferentUserCannotEditSeminar(): void
    {
        $organizer = $this->createUser(1, 'organizer@example.com');
        $otherUser = $this->createUser(2, 'other@example.com');
        $seminar = $this->createSeminar($organizer);

        $token = $this->createTokenMock($otherUser);

        self::assertTrue(
            $this->voter->vote($token, $seminar, [SeminarVoter::EDIT]) < 0
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDifferentUserCannotDeleteSeminar(): void
    {
        $organizer = $this->createUser(1, 'organizer@example.com');
        $otherUser = $this->createUser(2, 'other@example.com');
        $seminar = $this->createSeminar($organizer);

        $token = $this->createTokenMock($otherUser);

        self::assertTrue(
            $this->voter->vote($token, $seminar, [SeminarVoter::DELETE]) < 0
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testAnonymousUserCannotEditSeminar(): void
    {
        $organizer = $this->createUser(1, 'organizer@example.com');
        $seminar = $this->createSeminar($organizer);

        $token = $this->createTokenMock(null);

        self::assertTrue(
            $this->voter->vote($token, $seminar, [SeminarVoter::EDIT]) < 0
        );
    }

    private function createUser(int $id, string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('hashed-password');
        $user->setRoles(['ROLE_USER']);

        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setValue($user, $id);

        return $user;
    }

    private function createSeminar(User $organizer): Seminar
    {
        $seminar = new Seminar();
        $seminar->setTitle('Test Seminar');
        $seminar->setDescription('Test Beschreibung');
        $seminar->setStartDate(new \DateTimeImmutable('+10 days'));
        $seminar->setEndDate(new \DateTimeImmutable('+11 days'));
        $seminar->setRegistrationDeadline(new \DateTimeImmutable('+5 days'));
        $seminar->setMaxParticipants(20);
        $seminar->setOrganizer($organizer);

        return $seminar;
    }

    private function createTokenMock(mixed $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }
}