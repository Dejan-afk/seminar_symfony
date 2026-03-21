<?php

namespace App\DataFixtures;

use App\Entity\Seminar;
use App\Entity\Session;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $users = [];

        for ($i = 1; $i <= 8; $i++) {
            $user = new User();
            $user->setEmail(sprintf('organizer%d@example.com', $i));
            $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
            $users[] = $user;
        }

        for ($i = 1; $i <= 100; $i++) {
            $seminar = new Seminar();

            $startDate = new \DateTimeImmutable(sprintf('+%d days 09:00', random_int(1, 180)));
            $endDate = $startDate->modify(sprintf('+%d days 17:00', random_int(0, 2)));
            $registrationDeadline = $startDate->modify(sprintf('-%d days', random_int(1, 21)));

            $seminar->setTitle(sprintf('Seminar %d', $i));
            $seminar->setDescription(sprintf('Beschreibung für Seminar %d', $i));
            $seminar->setStartDate($startDate);
            $seminar->setEndDate($endDate);
            $seminar->setRegistrationDeadline($registrationDeadline);
            $seminar->setMaxParticipants(random_int(10, 40));
            $seminar->setOrganizer($users[array_rand($users)]);

            $sessionCount = random_int(1, 5);

            for ($j = 1; $j <= $sessionCount; $j++) {
                $session = new Session();

                $sessionDay = $startDate->modify(sprintf('+%d days', random_int(0, max(0, $startDate->diff($endDate)->days))));
                $sessionStartHour = random_int(9, 15);
                $sessionStart = $sessionDay->setTime($sessionStartHour, 0);
                $sessionEnd = $sessionStart->modify(sprintf('+%d hours', random_int(1, 3)));

                if ($sessionEnd > $endDate->setTime(18, 0)) {
                    $sessionEnd = $sessionDay->setTime(18, 0);
                }

                $session->setTitle(sprintf('Session %d.%d', $i, $j));
                $session->setStartsAt($sessionStart);
                $session->setEndsAt($sessionEnd);

                $seminar->addSession($session);
            }

            $manager->persist($seminar);
        }

        $manager->flush();
    }
}