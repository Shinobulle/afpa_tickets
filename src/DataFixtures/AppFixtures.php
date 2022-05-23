<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Ticket;
use Doctrine\Persistence\ObjectManager;
use App\Faker\Provider\ImmutableDateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création entre 30 et 50 tickets aléatoirement
        for ($t = 0; $t < mt_rand(30, 50); $t++) {

            // Création d'un nouveau ticket    
            $ticket = new Ticket;

            // On nourrit l'objet Ticket
            $ticket->setMessage($faker->paragraph(3))
                ->setComment($faker->paragraph(3))
                ->setIsActive($faker->boolean(75))
                ->setCreateAt(new \DateTimeImmutable())
                ->setFinishedAt(!$ticket->getIsActive() ? ImmutableDateTime::immutableDateTimeBetween('now', '6 months') : null)
                ->setObject($faker->sentence(6));

            // On fait persister les données                  
            $manager->persist($ticket);
        }

        $manager->flush();
    }
}
