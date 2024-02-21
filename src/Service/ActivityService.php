<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class ActivityService
{
    private EntityManagerInterface $entityManager;
    private $projectDir;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->projectDir = $kernel->getProjectDir();
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

    public function generateJson($jsonData): Response
    {
        dd($jsonData);
        // $data = [
        //     'name' => 'John Doe',
        //     'age' => 30,
        //     'email' => 'john@example.com'
        // ];

        $response = new Response($jsonData);

        // Enregistrement du contenu JSON dans un fichier
        $filePath = $this->projectDir . '/public/scenarios/activity.json'; // Chemin où vous souhaitez enregistrer le fichier JSON
        file_put_contents($filePath, $jsonData);

        // Réponse de confirmation
        return new Response('Fichier JSON généré avec succès');
    }
}
