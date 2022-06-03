<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Ticket;
use App\Entity\Department;
use Doctrine\Persistence\ObjectManager;
use App\Faker\Provider\ImmutableDateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    protected UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création de 5 utilisateurs
        for ($u = 0; $u < 5; $u++) {
            // Création d'un user     
            $user = new User;

            // Hashage de notre mot de passe avec le paramètres de sécurité de notre User
            //Dans /config/packages/security.yaml
            $hash = $this->hasher->hashPassword($user, "test");

            // Si premier utilisateur créé, on lui donne le rôle d'admin 
            if ($u == 0) {
                $user->setRoles(["ROLE_ADMIN"])
                    ->setEmail("admin@test.fr");
            } else {
                $user->setEmail("user{$u}@test.test");
            }

            // On nourrit l'objet User 

            $user->setName($faker->name())
                ->setPassword($hash);

            //On fait persister les données
            $manager->persist($user);
        }

        // Création de 10 departments
        for ($d = 0; $d < 10; $d++) {
            // Création d'un nouvel objet department

            $department = new Department;

            // On nourrit l'objet department
            $department->setName($faker->company());

            // On persist notre objet department
            $manager->persist($department);
        }

        // On push les departments en BDD 
        $manager->flush();

        $allDepartments = $manager->getRepository(Department::class)->findAll();
        $allUsers = $manager->getRepository(User::class)->findAll();
        // Création entre 30 et 50 tickets aléatoirement
        for ($t = 0; $t < mt_rand(30, 50); $t++) {

            // Création d'un nouveau ticket    
            $ticket = new Ticket;



            // On nourrit l'objet Ticket
            $ticket->setMessage($faker->paragraph(3))
                ->setComment($faker->paragraph(3))
                ->setTicketStatut('initial')
                ->setUser($faker->randomElement($allUsers))
                ->setCreateAt(new \DateTimeImmutable())
                ->setFinishedAt(!$ticket->getTicketStatut() == 'finished' ? ImmutableDateTime::immutableDateTimeBetween('now', '6 months') : null)
                ->setObject($faker->sentence(6))
                ->setDepartment($faker->randomElement($allDepartments));

            // On fait persister les données                  
            $manager->persist($ticket);
        }

        $manager->flush();
    }
}
