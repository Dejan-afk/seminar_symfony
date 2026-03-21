<?php

namespace App\Service;

use App\Dto\SeminarUpdateDto;
use App\Entity\Seminar;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;

class SeminarUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function update(Seminar $seminar, SeminarUpdateDto $dto): Seminar
    {
        $seminar->setTitle($dto->title);
        $seminar->setDescription($dto->description);
        $seminar->setStartDate($dto->startDate);
        $seminar->setEndDate($dto->endDate);
        $seminar->setRegistrationDeadline($dto->registrationDeadline);
        $seminar->setMaxParticipants($dto->maxParticipants);

        foreach ($seminar->getSessions()->toArray() as $existingSession) {
            $seminar->removeSession($existingSession);
        }

        foreach ($dto->sessions as $sessionDto) {
            $session = new Session();
            $session->setTitle($sessionDto->title);
            $session->setStartsAt($sessionDto->startsAt);
            $session->setEndsAt($sessionDto->endsAt);

            $seminar->addSession($session);
        }

        $this->entityManager->flush();

        return $seminar;
    }
}