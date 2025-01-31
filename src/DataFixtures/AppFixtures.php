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
            ->setFirstName('Jean')
            ->setLastName('Dupont')
            ->setRoles(['ROLE_ADMIN'])
            ->setSector($sector1)
            ->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $dirigeant = new User();
        $dirigeant->setEmail('dirigeant@example.com')
            ->setFirstName('Marie')
            ->setLastName('Leclerc')
            ->setRoles(['ROLE_DIRIGEANT'])
            ->setSector($sector1)
            ->setPassword($this->passwordHasher->hashPassword($dirigeant, 'dirigeant123'));
        $manager->persist($dirigeant);

        $enseignant = new User();
        $enseignant->setEmail('enseignant@example.com')
            ->setFirstName('Paul')
            ->setLastName('Bernard')
            ->setRoles(['ROLE_ENSEIGNANT'])
            ->setSector($sector2)
            ->setPassword($this->passwordHasher->hashPassword($enseignant, 'enseignant123'));
        $manager->persist($enseignant);

        // Création des églises
        $church1 = new Church();
        $church1->setName('Église Saint-Pierre')
            ->setAddress('12 Place de l’Église, 75001 Paris')
            ->setSector($sector1);
        $manager->persist($church1);

        $church2 = new Church();
        $church2->setName('Église Saint-Paul')
            ->setAddress('34 Rue des Chapelles, 69002 Lyon')
            ->setSector($sector1);
        $manager->persist($church2);

        $church3 = new Church();
        $church3->setName('Église Notre-Dame')
            ->setAddress('56 Avenue du Sacré-Cœur, 13003 Marseille')
            ->setSector($sector2);
        $manager->persist($church3);

        // Création des jeunes
        $noms = ['Martin', 'Lefebvre', 'Moreau', 'Girard', 'Simon'];
        $prenoms = ['Lucas', 'Emma', 'Noah', 'Léa', 'Gabriel'];
        
        $youths = [];
        foreach ([$church1, $church2, $church3] as $church) {
            for ($i = 0; $i < 5; $i++) {
                $youth = new Youth();
                $youth->setFirstName($prenoms[$i])
                    ->setLastName($noms[$i])
                    ->setAddress('Rue des Jeunes ' . ($i + 1))
                    ->setBirthDate(new \DateTime('-' . (16 + $i) . ' years'))
                    ->setPhone("06" . rand(10000000, 99999999))
                    ->setChurch($church);
                $manager->persist($youth);
                $youths[] = $youth;
            }
        }

        // Création des événements
        $events = [];
        $eventNames = ['Réunion de prière', 'Concert Gospel', 'Conférence jeunesse', 'Camp d’été', 'Atelier de solidarité'];
        for ($i = -2; $i <= 2; $i++) {
            $event = new Event();
            $event->setName($eventNames[$i + 2])
                ->setDate(new \DateTime(($i * 7) . ' days'))
                ->setLocation("Salle " . ($i + 3))
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
