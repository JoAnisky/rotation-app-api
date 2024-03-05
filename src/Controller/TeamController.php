<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ActivityRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/teams')]
class TeamController extends AbstractController
{
    #[Route('/', name: 'teams', methods: ['GET'])]
    public function getTeamsList(TeamRepository $teamRepository, SerializerInterface $serializer): JsonResponse
    {
        $teamsList = $teamRepository->findAll();
        $jsonTeamsList = $serializer->serialize($teamsList, 'json', ['groups' => 'getTeams']);
        return new JsonResponse($jsonTeamsList, Response::HTTP_OK, [], true);
    }

    #[Route('/details/{id}', name: 'detail_team', methods: ['GET'])]
    public function getOneTeam(Team $team, SerializerInterface $serializer): JsonResponse
    {
        // If team doesn't ParamConverter will throw an Exception

        // Turn $team object into JSON format
        $jsonTeam = $serializer->serialize($team, 'json', ['groups' => 'getTeams']);
        return new JsonResponse($jsonTeam, Response::HTTP_OK, [], true);
    }

    #[Route('/delete/{id}', name: 'delete_team', methods: ['DELETE'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de suppression')]
    public function deleteTeam(Team $team, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($team);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a Team using the following structure 
     * 
     * {
     *  "name" : "teamName",
     *  "user" : 10 (needed for the moment, see AuthenticationService)
     * }
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param ActivityRepository $activityRepository
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/create', name: 'create_team', methods: ['POST'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de création')]
    public function createTeam(Request $request, UserRepository $userRepository, ActivityRepository $activityRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        // Create new team object with data provided
        $team = $serializer->deserialize($request->getContent(), Team::class, 'json');

        // Decode request content
        $requestData = json_decode($request->getContent(), true);

        // Setting User and Activity to the Team
        if (!empty($requestData['user'])) {
            $user = $userRepository->find($requestData['user']);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            // Associate found User with the new Team
            $team->setUser($user);
        }

        // check if requested Activity exists
        if (!empty($requestData['activity'])) {
            $activity = $activityRepository->find($requestData['activity']);
            if (!$activity) {
                return new JsonResponse(['error' => 'Activity not found'], Response::HTTP_NOT_FOUND);
            }
            // Set found Activity to the team
            $team->setActivity($activity);
        }

        // Validate the Team entity before flush
        $errors = $validator->validate($team);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Persist and flush new Team
        $em->persist($team);
        $em->flush();

        // Generate the "detail" URL for the new Team
        $location = $urlGenerator->generate('detail_team', ['id' => $team->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Serialize the new Team for the response
        $jsonTeam = $serializer->serialize($team, 'json', ['groups' => 'getTeams']);

        // return 201 with new Team and details URL
        return new JsonResponse($jsonTeam, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * PUT an existing team
     * {
     *  "name" : "newLogin",
     *  "activity_id" : 27
     * }
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param Team $currentTeam
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/update/{id}', name: 'update_team', methods: ['PUT'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de modification')]
    public function updateTeam(Request $request, ActivityRepository $activityRepository, Team $currentTeam, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {

        // Extracting activity ID from the request content
        $requestData = json_decode($request->getContent(), true);

         // check if requested Activity exists
         if (!empty($requestData['activity'])) {
            $activity = $activityRepository->find($requestData['activity']);
            if (!$activity) {
                return new JsonResponse(['error' => 'Activity not found'], Response::HTTP_NOT_FOUND);
            }
            // Set found Activity to the team
            $currentTeam->setActivity($activity);
        }

        // Deserialization and Update Team without Activity
        $serializer->deserialize(
            $request->getContent(),
            Team::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentTeam, 'ignored_attributes' => ['activity']]
        );

        /* The updated team object is validated using the ValidatorInterface to ensure the data is valid.
        *   If any errors are found, a JSON response with the errors is returned with a HTTP_BAD_REQUEST status
        */
        $errors = $validator->validate($currentTeam);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();

        // A JsonResponse with a HTTP_NO_CONTENT status code 204 is returned, indicating successful update without any content in the response body.
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
