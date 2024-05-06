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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /** PUT an existing scenario
     * {
     * "scenario_id" : 27
     * }
     * 
     * @param Request $request
     * @param Scenario $currentScenario
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'update_scenario', methods: ['PUT'])]
    // #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de modification')]
    public function updateScenario(Request $request, Scenario $currentScenario, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {

        // Deserialization and Update Scenario
        $serializer->deserialize($request->getContent(), Scenario::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentScenario]);

        /* The updated scenario object is validated using the ValidatorInterface to ensure the data is valid.
        *  If any errors are found, a JSON response with the errors is returned with a HTTP_BAD_REQUEST status*/
        $errors = $validator->validate($currentScenario);

        if ($errors->count() > 0) {
            // Srialize data
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
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
    public function generateScenarioAction(Activity $activity, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        // Retrieve teams and stands from the activity ID
        $teams = $activity->getTeams(); //  retrieves the teams 
        $stands = $activity->getStands(); //  retrieves the stands 

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
        $rotationsJSON = $serializer->serialize($rotationResult["data"], 'json', ['groups' => 'getActivity']);

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

    /**
     * Performs rotations and returns a scenario array with teams and stands for each turn
     * 
     * @param array $teams - Array of teams
     * @param array $stands - Array of stands
     * @return array
     */
    private function generateRotations(array $teams, array $stands): array
    {
        $rotations = [];
        $teamIds = array_column($teams, 'id');
        $teamNames = array_combine($teamIds, array_column($teams, 'name'));
        $standIds = array_column($stands, 'id');
        $standNames = array_combine($standIds, array_column($stands, 'name'));

        // Initialization of the variable for cumulative stand capacities
        $nbSlots = 0;
        $nbSlotsCompetitive = 0;
        // Used to compare stand capacity
        $firstStandCapacity = $stands[0]['nbTeamsOnStand'];
        // Search for competitive stands (nbTeamsOnStand > 1)
        $competitiveStands = [];

        // Everything has to be either compete or solo,
        foreach ($stands as $stand) {
            $currentStandCapacity = $stand['nbTeamsOnStand'];
            if ($currentStandCapacity !== $firstStandCapacity) {
                return ['success' => false, 'details' => "Tous les stands doivent accueillir le même nombre d'équipes (capacité)."];
            }

            if ($currentStandCapacity > 1) {
                $competitiveStands[] = $stand;
                $nbSlotsCompetitive += $currentStandCapacity;
            }
            $nbSlots += $currentStandCapacity;  // Add nbSlots based on nbTeamsOnStand for each stand

        }

        $teamCount = count($teamIds);

        // Check whether the total number of competitive slots is divisible by the number of teams
        if ($nbSlotsCompetitive % $teamCount !== 0) {
            return ['success' => false, 'details' => "Le nombre total de slots compétitifs doit être divisible par le nombre d'équipes."];
        }

        // Number of teams less than or equal to number of slots
        if ($teamCount > $nbSlots) {
            return ['success' => false, 'details' => "Le nombre d'équipes doit être inférieur ou egal au nombre total d'emplacements des stands"];
        }

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

        // Calcul du nombre total de manches nécessaire
        $nbRounds = ceil(count($stands) / $teamCount);

        // Rotation pour chaque manche
        for ($round = 0; $round < $nbRounds; $round++) {

            $endStandIndex = ($teamCount / 2);
            for ($turnNumber = 0; $turnNumber < count($standIds); $turnNumber++) {
                $currentRound = [];
                $newPositions = [];
                $usedCapacities = array_fill_keys($standIds, 0); // Réinitialisation des capacités pour le nouveau tour

                // Rotation de chaque équipe en fonction de son statut pair ou impair
                foreach ($initialPositions as $teamId => $standId) {
                    $standIndex = array_search($standId, $standIds); // Récupération de l'index du stand actuel

                    // Vérifier si le stand actuel est dans la plage de stands pour ce tour
                    if ($standIndex < $endStandIndex) {
                        $stand = $stands[$standIndex];
                        $moveUp = ($teamId % 2 == 0);

                        $nextStandIndex = $standIndex;
                        // Avancer ou reculer à l'index suivant en fonction du statut pair ou impair de l'équipe
                        do {
                            if ($moveUp) {
                                $nextStandIndex = ($nextStandIndex + 1) % count($standIds);
                            } else {
                                $nextStandIndex = ($nextStandIndex - 1 + count($standIds)) % count($standIds);
                            }

                            // Vérifier si le stand suivant respecte les capacités avant de placer l'équipe
                        } while ($usedCapacities[$standIds[$nextStandIndex]] >= $stands[$nextStandIndex]['nbTeamsOnStand']);

                        // Placez l'équipe sur le stand approprié et mettez à jour les informations
                        $newPositions[$teamId] = $standIds[$nextStandIndex];
                        $usedCapacities[$standIds[$nextStandIndex]]++;
                        $currentRound[$standNames[$standIds[$nextStandIndex]]][] = $teamNames[$teamId];
                    }
                }

                // Mettre à jour les positions pour le prochain tour
                $initialPositions = $newPositions;
                $rotations[] = $currentRound;
            }
        }

        return ['success' => true, 'data' => $rotations];
    }
}
