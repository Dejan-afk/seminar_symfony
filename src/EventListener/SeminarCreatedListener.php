<?php

namespace App\EventListener;

use App\Event\SeminarCreatedEvent;
use Psr\Log\LoggerInterface;

class SeminarCreatedListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(SeminarCreatedEvent $event): void
    {
        $this->logger->info('Seminar created', [
            'seminarId' => $event->seminar->getId(),
            'title' => $event->seminar->getTitle(),
        ]);
    }
}