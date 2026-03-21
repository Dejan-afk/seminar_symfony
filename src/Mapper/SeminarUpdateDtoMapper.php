<?php

namespace App\Mapper;

use App\Dto\SeminarUpdateDto;
use App\Dto\SessionInputDto;
use App\Entity\Seminar;

class SeminarUpdateDtoMapper
{
    public function mapEntityToDto(Seminar $seminar): SeminarUpdateDto
    {
        $dto = new SeminarUpdateDto();
        $dto->title = $seminar->getTitle();
        $dto->description = $seminar->getDescription();
        $dto->startDate = $seminar->getStartDate();
        $dto->endDate = $seminar->getEndDate();
        $dto->registrationDeadline = $seminar->getRegistrationDeadline();
        $dto->maxParticipants = $seminar->getMaxParticipants();

        foreach ($seminar->getSessions() as $session) {
            $sessionDto = new SessionInputDto();
            $sessionDto->title = $session->getTitle();
            $sessionDto->startsAt = $session->getStartsAt();
            $sessionDto->endsAt = $session->getEndsAt();

            $dto->sessions[] = $sessionDto;
        }

        return $dto;
    }
}