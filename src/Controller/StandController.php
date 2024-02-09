<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Stand;
use App\Repository\ActivityRepository;
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
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/', name: 'create_stand', methods: ['POST'])]
    public function createStand(Request $request, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        // Extracting user ID from the request content
        $requestData = json_decode($request->getContent(), true);
        $userId = $requestData['user'];

        // check if associated User exists
        $user = $userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }


        $stand = $serializer->deserialize($request->getContent(), Stand::class, 'json');

        // Associate found User with the new Stand
        $stand->setUser($user);

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
     * PUT an existing stand
     * {
     *  "name" : "newLogin",
     *  "activity_id" : 27
     * }
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param Stand $currentStand
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'update_stand', methods: ['PUT'])]
    public function updateStand(Request $request, UserRepository $userRepository, ActivityRepository $activityRepository, Stand $currentStand, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {

        // Check if the user exists
        $user = $userRepository->find($currentStand->getUser());
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Extracting activity ID from the request content
        $requestData = json_decode($request->getContent(), true);

        // if no activity_id provided, null by default
        $activityId = $requestData['activity'] ?? null;

        // check if requested Activity exists
        if ($activityId !== null) {
            $activity = $activityRepository->find($activityId);
            if (!$activity) {
                return new JsonResponse(['error' => 'Activity not found'], Response::HTTP_NOT_FOUND);
            }
            $currentStand->setActivity($activity);
        }

        $updatedStand = $serializer->deserialize(
            $request->getContent(),
            Stand::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentStand]
        );

        // Update User and activity field
        $currentStand->setUser($user)
            ->setActivity($activity);

        /* The updated stand object is validated using the ValidatorInterface to ensure the data is valid.
        *   If any errors are found, a JSON response with the errors is returned with a HTTP_BAD_REQUEST status
        */
        $errors = $validator->validate($updatedStand);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();

        // A JsonResponse with a HTTP_NO_CONTENT status code 204 is returned, indicating successful update without any content in the response body.
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
