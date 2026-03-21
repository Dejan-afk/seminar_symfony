<?php

namespace App\Service;

use App\Dto\SeminarCreateDto;
use App\Entity\Seminar;
use App\Entity\Session;
use App\Entity\User;
use App\Event\SeminarCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SeminarCreator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function create(SeminarCreateDto $dto, User $organizer): Seminar
    {
        $seminar = new Seminar();
        $seminar->setTitle($dto->title);
        $seminar->setDescription($dto->description);
        $seminar->setStartDate($dto->startDate);
        $seminar->setEndDate($dto->endDate);
        $seminar->setRegistrationDeadline($dto->registrationDeadline);
        $seminar->setMaxParticipants($dto->maxParticipants);
        $seminar->setOrganizer($organizer);

        foreach ($dto->sessions as $sessionDto) {
            $session = new Session();
            $session->setTitle($sessionDto->title);
            $session->setStartsAt($sessionDto->startsAt);
            $session->setEndsAt($sessionDto->endsAt);

            $seminar->addSession($session);
        }

        $this->entityManager->persist($seminar);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new SeminarCreatedEvent($seminar));

        return $seminar;
    }
}