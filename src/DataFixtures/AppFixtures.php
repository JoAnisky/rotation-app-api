<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un user avec le "ROLE_USER" uniquement
        $user = new User();
        $user->setLogin("admin")
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->userPasswordHasher->hashPassword($user, "admin"));
        $manager->persist($user);

        // Création d'un user avec le ROLE_ADMIN
        $userAdmin = new User();
        $userAdmin->setLogin("gamemaster")
            ->setRoles(['ROLE_GAMEMASTER'])
            ->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "gamemaster"));
        $manager->persist($userAdmin);

        $manager->flush();
    }
}
