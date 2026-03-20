<?php

namespace App\Event;

use App\Entity\Seminar;

class SeminarCreatedEvent
{
    public function __construct(
        public readonly Seminar $seminar
    ) {
    }
}