<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Animator;
use App\Entity\Stand;
use App\Entity\Team;
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
        $this->createUsers($manager);
    }

    public function createUsers(ObjectManager $manager): void
    {
        // Admin User creation 
        $userAdmin = new User();
        $userAdmin->setLogin("admin")
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "admin"));
        $manager->persist($userAdmin);

        // Gamemaster User creation 
        $userGamemaster = new User();
        $userGamemaster->setLogin("gamemaster")
            ->setRoles(['ROLE_GAMEMASTER'])
            ->setPassword($this->userPasswordHasher->hashPassword($userGamemaster, "gamemaster"));
        $manager->persist($userGamemaster);

        $manager->flush();

        // Create Animators
        $this->createAnimators($manager, $userGamemaster);
    }

    public function createAnimators(ObjectManager $manager, User $user): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $animator = new Animator();
            $animator->setUser($user)
                ->setName("Animateur $i");
            $manager->persist($animator);
        }
        $manager->flush();
        $this->createTeams($manager, $user);
    }

    public function createTeams(ObjectManager $manager, User $user): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $team = new Team();
            $team->setUser($user)
                ->setName("Team $i");
            $manager->persist($team);
        }
        $manager->flush();
        $this->createActivities($manager, $user);
    }

    public function createActivities(ObjectManager $manager, User $user): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $activity = new Activity();
            $activity->setUser($user)
                ->setName("Activité $i");
            $manager->persist($activity);
        }
        $manager->flush();
        $this->createStands($manager, $user, $activity);
    }

    public function createStands(ObjectManager $manager, User $user, Activity $activity): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $stand = new Stand();
            $stand->setUser($user)
                ->setActivity($activity)
                ->setName("Stand $i");
            $manager->persist($stand);
        }
        $manager->flush();
    }
}
