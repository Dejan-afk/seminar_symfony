<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Seminar;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\SeminarRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SeminarControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private SeminarRepository $seminarRepository;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->seminarRepository = $container->get(SeminarRepository::class);
    }

    public function testGuestIsRedirectedFromNew(): void
    {
        $this->client->request('GET', '/seminars/new');

        self::assertResponseRedirects('/login');
    }

    public function testIndexShowsExistingSeminars(): void
    {
        $organizer = $this->createUser('index_organizer_' . uniqid() . '@example.com');
        $this->createSeminar($organizer, 'Index Seminar');

        $this->client->request('GET', '/seminars/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Index Seminar', $this->client->getResponse()->getContent());
    }

    public function testLoggedInUserCanCreateSeminar(): void
    {
        $user = $this->createUser('creator_' . uniqid() . '@example.com');
        $this->client->loginUser($user);

        $title = 'Created Seminar ' . uniqid();

        $this->client->request('GET', '/seminars/new');

        $this->client->submitForm('Seminar erstellen', [
            'seminar[title]' => $title,
            'seminar[description]' => 'Beschreibung für ein neues Seminar',
            'seminar[startDate]' => '2030-01-10 09:00',
            'seminar[endDate]' => '2030-01-10 17:00',
            'seminar[registrationDeadline]' => '2030-01-05 12:00',
            'seminar[maxParticipants]' => 25,
            'seminar[sessions][0][title]' => 'Session 1',
            'seminar[sessions][0][startsAt]' => '2030-01-10 10:00',
            'seminar[sessions][0][endsAt]' => '2030-01-10 12:00',
        ]);

        self::assertResponseRedirects();

        $this->entityManager->clear();

        $seminar = $this->seminarRepository->findOneBy(['title' => $title]);
        self::assertNotNull($seminar);
        self::assertSame($user->getId(), $seminar->getOrganizer()?->getId());
    }

    public function testOrganizerCanEditOwnSeminar(): void
    {
        $organizer = $this->createUser('edit_owner_' . uniqid() . '@example.com');
        $seminar = $this->createSeminar($organizer, 'Old Title');

        $this->client->loginUser($organizer);
        $this->client->request('GET', '/seminars/' . $seminar->getId() . '/edit');

        $this->client->submitForm('Änderungen speichern', [
            'seminar[title]' => 'Updated Title',
            'seminar[description]' => 'Aktualisierte Beschreibung',
            'seminar[startDate]' => '2030-02-10 09:00',
            'seminar[endDate]' => '2030-02-10 17:00',
            'seminar[registrationDeadline]' => '2030-02-05 12:00',
            'seminar[maxParticipants]' => 50,
            'seminar[sessions][0][title]' => 'Updated Session',
            'seminar[sessions][0][startsAt]' => '2030-02-10 10:00',
            'seminar[sessions][0][endsAt]' => '2030-02-10 12:00',
        ]);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $updated = $this->seminarRepository->find($seminar->getId());

        self::assertSame('Updated Title', $updated?->getTitle());
        self::assertSame(50, $updated?->getMaxParticipants());
    }

    public function testNonOrganizerCannotEditSeminar(): void
    {
        $organizer = $this->createUser('edit_real_owner_' . uniqid() . '@example.com');
        $otherUser = $this->createUser('edit_other_' . uniqid() . '@example.com');
        $seminar = $this->createSeminar($organizer, 'Protected Seminar');

        $this->client->loginUser($otherUser);
        $this->client->request('GET', '/seminars/' . $seminar->getId() . '/edit');

        self::assertResponseStatusCodeSame(403);
    }

    public function testOrganizerCanDeleteOwnSeminar(): void
    {
        $organizer = $this->createUser('delete_owner_' . uniqid() . '@example.com');
        $seminar = $this->createSeminar($organizer, 'Delete Me ' . uniqid());

        $this->client->loginUser($organizer);
        $crawler = $this->client->request('GET', '/seminars/');

        $form = $crawler->filter(sprintf('form[action="/seminars/%d"]', $seminar->getId()))->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/seminars/');

        $this->entityManager->clear();
        self::assertNull($this->seminarRepository->find($seminar->getId()));
    }

    public function testNonOrganizerCannotDeleteSeminar(): void
    {
        $organizer = $this->createUser('delete_real_owner_' . uniqid() . '@example.com');
        $otherUser = $this->createUser('delete_other_' . uniqid() . '@example.com');
        $seminar = $this->createSeminar($organizer, 'Cannot Delete Me ' . uniqid());

        $this->client->loginUser($otherUser);
        $crawler = $this->client->request('GET', '/seminars/');

        $form = $crawler->filter(sprintf('form[action="/seminars/%d"]', $seminar->getId()))->form();
        $this->client->submit($form);

        self::assertResponseStatusCodeSame(403);

        $this->entityManager->clear();
        self::assertNotNull($this->seminarRepository->find($seminar->getId()));
    }

    public function testDeleteRequiresValidCsrfToken(): void
    {
        $organizer = $this->createUser('csrf_owner_' . uniqid() . '@example.com');
        $seminar = $this->createSeminar($organizer, 'Csrf Protected');

        $this->client->loginUser($organizer);

        $this->client->request('POST', '/seminars/' . $seminar->getId(), [
            '_token' => 'invalid-token',
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->entityManager->clear();
        self::assertNotNull($this->seminarRepository->find($seminar->getId()));
    }

    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('hashed-password');
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createSeminar(User $organizer, string $title): Seminar
    {
        $seminar = new Seminar();
        $seminar->setTitle($title);
        $seminar->setDescription('Beschreibung');
        $seminar->setStartDate(new \DateTimeImmutable('2030-01-10 09:00'));
        $seminar->setEndDate(new \DateTimeImmutable('2030-01-10 17:00'));
        $seminar->setRegistrationDeadline(new \DateTimeImmutable('2030-01-05 12:00'));
        $seminar->setMaxParticipants(20);
        $seminar->setOrganizer($organizer);

        $session = new Session();
        $session->setTitle('Initial Session');
        $session->setStartsAt(new \DateTimeImmutable('2030-01-10 10:00'));
        $session->setEndsAt(new \DateTimeImmutable('2030-01-10 12:00'));

        $seminar->addSession($session);

        $this->entityManager->persist($seminar);
        $this->entityManager->flush();

        return $seminar;
    }
}