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
    public function getDetailScenario(int $id, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $scenarioRepository = $em->getRepository(Scenario::class);  // Assuming Scenario is your entity class
        $scenario = $scenarioRepository->findScenarioByActivityId($id);

        if (!$scenario) {
            // If no scenario found, return a JSON response with an error message
            return new JsonResponse([
                'success' => false,
                'message' => 'Pas de scénario trouvé'
            ], Response::HTTP_NOT_FOUND);  // Using HTTP 404 Not Found status
        }

        // Serialize the scenario object into JSON format
        $jsonScenario = $serializer->serialize($scenario, 'json', [
            'groups' => 'getScenario'
        ]);

        // Return a successful JSON response with the serialized scenario
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
        //$battleStands = $activityRepository->findCompetitiveStands($activityId);

        if (empty($teams)) {
            return new JsonResponse(['message' => 'Pas d\'équipes trouvées'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($stands)) {
            return new JsonResponse(['message' => 'Pas de stands trouvés'], Response::HTTP_BAD_REQUEST);
        }
        // If teams and Stand have been found, Generate scenario
        $rotationResult = $this->generateRotations($teams, $stands);

        if (!$rotationResult['success']) {
            return new JsonResponse([
                'success' => $rotationResult['success'],
                'message' => 'Impossible de générer le scénario',
                'details' => $rotationResult['details']
            ], Response::HTTP_BAD_REQUEST);
        }
        // Convertir les rotations en JSON
        $rotationsJSON = $serializer->serialize($rotationResult, 'json', ['groups' => 'getActivity']);

        // Vérify if a scenario already exists for this Activity
        $scenario = $em->getRepository(Scenario::class)->findOneBy(['activity' => $activity]);

        if (!$scenario) {
            // No scenario, let's create it !
            $scenario = new Scenario();
            $scenario->setActivity($activity);
        }

        // Convert JSON to array
        $rotationsArray = json_decode($rotationsJSON, true);
        $scenario->setBaseScenario($rotationsArray);

        $em->persist($scenario);
        $em->flush($scenario);

        // If success, do something with $rotationResult['data']
        // For example, returning a successful response with data
        return new JsonResponse([
            'success' => true,
            'message' => 'Scenario des rotations créé',
            'data' => $rotationResult['data']
        ], Response::HTTP_OK);
    }


    private function generateRotations(array $teams, array $stands): array
    {
        $rotations = [];
        $teamIds = array_column($teams, 'id');
        $teamNames = array_combine($teamIds, array_column($teams, 'name'));
        $standIds = array_column($stands, 'id');
        $standNames = array_combine($standIds, array_column($stands, 'name'));

        //  Initialize team capacities and positions
        $initialPositions = [];
        $standCapacities = array_fill_keys($standIds, 0); // Array to keep track of the number of teams per stand

        // Assign teams to pits initially while respecting capacity
        foreach ($teamIds as $teamId) {
            foreach ($stands as $stand) {
                if ($standCapacities[$stand['id']] < $stand['nbTeamsOnStand']) {
                    $initialPositions[$teamId] = $stand['id'];
                    $standCapacities[$stand['id']]++;
                    break;
                }
            }
        }

        // Rotation for each turn
        for ($turnNumber = 0; $turnNumber < count($stands); $turnNumber++) {
            $currentRound = [];
            $newPositions = [];
            $usedCapacities = array_fill_keys($standIds, 0); // Reset capacities for the new round

            // Rotate each team according to their pair or odd status
            foreach ($initialPositions as $teamId => $standId) {
                $currentStandIndex = array_search($standId, $standIds);
                $moveUp = ($teamId % 2 == 0);

                if ($moveUp) {
                    $nextStandIndex = ($currentStandIndex + 1) % count($standIds);
                } else {
                    $nextStandIndex = ($currentStandIndex - 1 + count($standIds)) % count($standIds);
                }

                // Check for capacity constraints before placing the team
                while ($usedCapacities[$standIds[$nextStandIndex]] >= $stands[$nextStandIndex]['nbTeamsOnStand']) {
                    if ($moveUp) {
                        $nextStandIndex = ($nextStandIndex + 1) % count($standIds);
                    } else {
                        $nextStandIndex = ($nextStandIndex - 1 + count($standIds)) % count($standIds);
                    }
                }

                $newPositions[$teamId] = $standIds[$nextStandIndex];
                $usedCapacities[$standIds[$nextStandIndex]]++;
                $currentRound[$standNames[$standIds[$nextStandIndex]]][] = $teamNames[$teamId];
            }

            $initialPositions = $newPositions; // Update positions for the next round
            $rotations[] = $currentRound;
        }

        return ['success' => true, 'data' => $rotations];
    }
}
