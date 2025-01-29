<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Sector;
use App\Entity\Church;
use App\Entity\Youth;
use App\Entity\Event;
use App\Entity\Attendance;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des secteurs
        $sector1 = new Sector();
        $sector1->setName('Secteur Nord');
        $manager->persist($sector1);

        $sector2 = new Sector();
        $sector2->setName('Secteur Sud');
        $manager->persist($sector2);

        // Création des utilisateurs
        $admin = new User();
        $admin->setEmail('admin@example.com')
            ->setFirstName('Admin')
            ->setLastName('User')
            ->setRoles(['ROLE_ADMIN'])
            ->setSector($sector1)
            ->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $dirigeant = new User();
        $dirigeant->setEmail('dirigeant@example.com')
            ->setFirstName('Dirigeant')
            ->setLastName('User')
            ->setRoles(['ROLE_DIRIGEANT'])
            ->setSector($sector1)
            ->setPassword($this->passwordHasher->hashPassword($dirigeant, 'dirigeant123'));
        $manager->persist($dirigeant);

        $enseignant = new User();
        $enseignant->setEmail('enseignant@example.com')
            ->setFirstName('Enseignant')
            ->setLastName('User')
            ->setRoles(['ROLE_ENSEIGNANT'])
            ->setSector($sector2)
            ->setPassword($this->passwordHasher->hashPassword($enseignant, 'enseignant123'));
        $manager->persist($enseignant);

        // Création des églises
        $church1 = new Church();
        $church1->setName('Église Saint-Pierre')
            ->setAddress('123 rue de la Paix')
            ->setSector($sector1);
        $manager->persist($church1);

        $church2 = new Church();
        $church2->setName('Église Saint-Paul')
            ->setAddress('456 avenue de la Liberté')
            ->setSector($sector1);
        $manager->persist($church2);

        $church3 = new Church();
        $church3->setName('Église Notre-Dame')
            ->setAddress('789 boulevard du Progrès')
            ->setSector($sector2);
        $manager->persist($church3);

        // Création des jeunes
        $youths = [];
        foreach ([$church1, $church2, $church3] as $church) {
            for ($i = 1; $i <= 5; $i++) {
                $youth = new Youth();
                $youth->setFirstName("Prénom$i")
                    ->setLastName("Nom$i")
                    ->setAddress("Adresse $i")
                    ->setBirthDate(new \DateTime("-" . (15 + $i) . " years"))
                    ->setPhone("060000000$i")
                    ->setChurch($church);
                $manager->persist($youth);
                $youths[] = $youth;
            }
        }

        // Création des événements
        $events = [];
        for ($i = -2; $i <= 2; $i++) {
            $event = new Event();
            $event->setName("Événement " . ($i + 3))
                ->setDate(new \DateTime("$i weeks"))
                ->setLocation("Lieu " . ($i + 3))
                ->setSector($i < 0 ? $sector1 : $sector2)
                ->setCreatedAt(new \DateTime())
                ->setCreatedBy($admin);
            $manager->persist($event);
            $events[] = $event;
        }

        // Création des présences
        foreach ($events as $event) {
            if ($event->getDate() < new \DateTime()) {
                foreach ($youths as $youth) {
                    if ($youth->getChurch()->getSector() === $event->getSector()) {
                        $attendance = new Attendance();
                        $attendance->setEvent($event)
                            ->setYouth($youth)
                            ->setIsPresent(rand(0, 1) === 1)
                            ->setCreatedAt(new \DateTime())
                            ->setCreatedBy($admin);
                        $manager->persist($attendance);
                    }
                }
            }
        }

        $manager->flush();
    }
} 