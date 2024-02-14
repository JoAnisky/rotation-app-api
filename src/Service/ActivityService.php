<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ActivityService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * Nullifies the activity relationship for a collection of entities and updates the database.
     * 
     * @param array $entities Entities to update.
     * @return void
     */
    public function nullifyActivityRelations(array $entities): void
    {
        foreach ($entities as $entity) {
            $entity->setActivity(null);
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }
}
