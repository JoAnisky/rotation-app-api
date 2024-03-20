<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Stand;
use App\Repository\ActivityRepository;
use App\Repository\AnimatorRepository;
use App\Repository\UserRepository;
use App\Repository\StandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/stands')]
class StandController extends AbstractController
{
    #[Route('/', name: 'stands', methods: ['GET'])]
    public function getStandsList(StandRepository $standRepository, SerializerInterface $serializer): JsonResponse
    {
        $standsList = $standRepository->findAll();
        $jsonStandsList = $serializer->serialize($standsList, 'json', ['groups' => 'getStands']);
        return new JsonResponse($jsonStandsList, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'detail_stand', methods: ['GET'])]
    public function getOneStand(Stand $stand, SerializerInterface $serializer): JsonResponse
    {
        // If stand doesn't ParamConverter will throw an Exception

        // Turn $stand object into JSON format
        $jsonStand = $serializer->serialize($stand, 'json', ['groups' => 'getStands']);
        return new JsonResponse($jsonStand, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'delete_stand', methods: ['DELETE'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de suppression')]
    public function deleteStand(Stand $stand, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($stand);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a Stand using the following structure 
     * 
     * {
     *  "stand_id" : 1, (nullable)
     *  "activity_id" : 1, (nullable)
     *  "name" : "stand name",
     *  "user_id" : 10,
     *  "is_competitive" : true, (default to false)
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
    #[Route('/', name: 'create_stand', methods: ['POST'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de création')]
    public function createStand(Request $request, UserRepository $userRepository, ActivityRepository $activityRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $stand = $serializer->deserialize($request->getContent(), Stand::class, 'json');

        // Extracting user ID from the request content
        $requestData = json_decode($request->getContent(), true);

        // Setting User and Activity to the Team
        if (!empty($requestData['user'])) {
            $user = $userRepository->find($requestData['user']);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            // Associate found User with the new Stand
            $stand->setUser($user);
        }

        // check if requested Activity exists
        if (!empty($requestData['activity'])) {
            $activity = $activityRepository->find($requestData['activity']);
            if (!$activity) {
                return new JsonResponse(['error' => 'Activity not found'], Response::HTTP_NOT_FOUND);
            }
            // Set found Activity to the team
            $stand->setActivity($activity);
        }

        // Check if no validation error, if errors -> returns error 400
        $errors = $validator->validate($stand);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Persist and flush new Stand
        $em->persist($stand);
        $em->flush();

        // Sérialize new Stand for the response
        $jsonStand = $serializer->serialize($stand, 'json', ['groups' => 'getStands']);

        // Générerate URL "détails" for the new stand
        $location = $urlGenerator->generate('detail_stand', ['id' => $stand->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // return 201 with new Stand and details URL
        return new JsonResponse($jsonStand, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * PUT an existing Stand
     * {
     *  "name" : "newLogin",
     *  "activity" : 27,
     * "animator" : 85
     * }
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param ActivityRepository $activityRepository
     * @param AnimatorRepository $animatorRepository
     * @param Stand $currentStand
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'update_stand', methods: ['PUT'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de modification')]
    public function updateStand(Request $request, ActivityRepository $activityRepository, AnimatorRepository $animatorRepository, Stand $currentStand, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {

        // Extracting data from the request content
        $requestData = json_decode($request->getContent(), true);

        // Why there is no error and no update if activity_id = 0 ??
        // check if there is an Activity to update and if it exists
        if (!empty($requestData['activity'])) {
            $activity = $activityRepository->find($requestData['activity']);
            if (!$activity) {
                return new JsonResponse(['error' => 'Activity not found'], Response::HTTP_NOT_FOUND);
            }
            // Set found Activity to the team
            $currentStand->setActivity($activity);
        }

        // check if there is an Animator to update and if it exists
        if (!empty($requestData['animator'])) {
            $animator = $animatorRepository->find($requestData['animator']);
            if (!$animator) {
                return new JsonResponse(['error' => 'Animator not found'], Response::HTTP_NOT_FOUND);
            }
            // Set found Animator to the team
            $currentStand->setAnimator($animator);
        }

        // Deserialization and Update Stand without Activity and Animator
        $serializer->deserialize(
            $request->getContent(),
            Stand::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentStand, 'ignored_attributes' => ['activity', 'animator']]
        );

        /* The updated team object is validated using the ValidatorInterface to ensure the data is valid.
        *   If any errors are found, a JSON response with the errors is returned with a HTTP_BAD_REQUEST status
        */
        $errors = $validator->validate($currentStand);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();

        // A JsonResponse with a HTTP_NO_CONTENT status code 204 is returned, indicating successful update without any content in the response body.
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
