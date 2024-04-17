<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Scenario;
use App\Repository\ActivityRepository;
use App\Repository\ScenarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/scenario')]
class ScenarioController extends AbstractController
{
    #[Route('/', name: 'scenario', methods: ['GET'])]
    public function getScenarios(ScenarioRepository $scenarioRepository, SerializerInterface $serializer): JsonResponse
    {
        $scenariosList = $scenarioRepository->findAll();
        $jsonScenariosList = $serializer->serialize($scenariosList, 'json', ['groups' => 'getScenario']);
        return new JsonResponse($jsonScenariosList, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'detail_scenario', methods: ['GET'])]
    public function getDetailScenario(int $id, ScenarioRepository $scenarioRepository, SerializerInterface $serializer): JsonResponse
    {
        $scenario = $scenarioRepository->findScenarioByActivityId($id);
        // If no scenario ParamConverter will throw an Exception
        // Turn $scenario object into JSON format
        $jsonScenario = $serializer->serialize($scenario, 'json', ['groups' => 'getScenario']);
        return new JsonResponse($jsonScenario, Response::HTTP_OK, [], true);
    }

    /** 
     * Generate a Scenario for this activity ID 
     * 
     * @param Request $request
     * @param Activity $currentActivity
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/{id}/generate', name: 'generate_scenario', methods: ['GET'])]
    public function generateScenarioAction(Activity $activity, ActivityRepository $activityRepository, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        // Retrieve teams and stands from the activity ID
        $teams = $activity->getTeams(); //  retrieves the teams 
        $stands = $activity->getStands(); //  retrieves the stands 

        $activityId = $activity->getId();
        $battleStands = $activityRepository->findCompetitiveStands($activityId);

        if (empty($teams)) {
            return new JsonResponse(['message' => 'Teams not found'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($stands)) {
            return new JsonResponse(['message' => 'Stands not found'], Response::HTTP_BAD_REQUEST);
        }
        // If teams and Stand have been found, Generate scenario
        $rotations = $this->generateRotations($teams, $stands, $battleStands);

        // Convertir les rotations en JSON
        $rotationsJSON = $serializer->serialize($rotations, 'json', ['groups' => 'getActivity']);

        // Vérify if a scenario already exists for this Activity
        $scenario = $em->getRepository(Scenario::class)->findOneBy(['activity' => $activity]);

        if ($scenario === null) {
            // No scenario, let's create it !
            $scenario = new Scenario();
            $scenario->setActivity($activity);
        }

        // Convert JSON to array
        $rotationsArray = json_decode($rotationsJSON, true);
        $scenario->setBaseScenario($rotationsArray);

        $em->persist($scenario);
        $em->flush($scenario);

        return new JsonResponse($rotationsJSON, Response::HTTP_OK, [], true);
    }

    private function generateRotations(array $teams, array $stands): array
    {
        $rotations = [];
        $teamIds = array_column($teams, 'id');
        $standIds = array_column($stands, 'id');

        $teamMap = array_combine($teamIds, $teams);
        $standMap = array_combine($standIds, $stands);

        $numTeams = count($teams);
        $numStands = count($stands);

        // Array to keep track of the current stand index for each team
        $currentStandIndices = array_fill(0, $numTeams, 0);

        // Initial assignment of teams to stands
        for ($i = 0; $i < $numTeams; $i++) {
            $currentStandIndices[$i] = $i % $numStands;
        }

        // Perform rotations for each turn
        for ($turnNumber = 0; $turnNumber < $numStands; $turnNumber++) {
            $currentRound = [];

            for ($i = 0; $i < $numTeams; $i++) {
                if ($teamIds[$i] % 2 == 0) {
                    // Team ID is even, move to the next stand
                    $currentStandIndices[$i] = ($currentStandIndices[$i] + 1) % $numStands;
                } else {
                    // Team ID is odd, move to the previous stand
                    $currentStandIndices[$i] = ($currentStandIndices[$i] - 1 + $numStands) % $numStands;
                }
                $currentStandId = $standIds[$currentStandIndices[$i]];
                $currentRound[$standMap[$currentStandId]['name']][] = $teamMap[$teamIds[$i]];
            }

            // Format the output for this round
            $rotations[] = $currentRound;
        }

        return $rotations;
    }
}
