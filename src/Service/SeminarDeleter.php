<?php

namespace App\Service;

use App\Entity\Seminar;
use Doctrine\ORM\EntityManagerInterface;

class SeminarDeleter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function delete(Seminar $seminar): void
    {
        $this->entityManager->remove($seminar);
        $this->entityManager->flush();
    }
}