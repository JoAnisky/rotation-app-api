<?php

namespace App\Controller;

use App\Entity\Stopwatch;
use App\Repository\ActivityRepository;
use App\Repository\StopwatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/stopwatch')]
class StopwatchController extends AbstractController
{

    #[Route('/', name: 'all_stopwatch', methods: ['GET'])]
    public function getStopwatchList(StopwatchRepository $stopwatchRepository, SerializerInterface $serializer): JsonResponse
    {
        $stopwatchList = $stopwatchRepository->findAll();
        $jsonStopwatchList = $serializer->serialize($stopwatchList, 'json', ['groups' => 'getStopwatch']);
        return new JsonResponse($jsonStopwatchList, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'detail_stopwatch', methods: ['GET'])]
    public function getStopwatch(Stopwatch $stopwatch, SerializerInterface $serializer): JsonResponse
    {
        // Turn $stopwatch object into JSON format
        $jsonStopwatch = $serializer->serialize($stopwatch, 'json', ['groups' => 'getStopwatch']);
        return new JsonResponse($jsonStopwatch, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Stopwatch using the following structure 
     * 
     * {
     *  "name" : "stopwatchName",
     *  "activity" : 10 (needed for the moment, see AuthenticationService)
     * }
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/', name: 'create_stopwatch', methods: ['POST'])]
    //#[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de création')]
    public function createStopwatch(Request $request, ActivityRepository $activityRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        // Create new stopwatch object with data provided
        $stopwatch = $serializer->deserialize($request->getContent(), Stopwatch::class, 'json');

        // Decode request content
        $requestData = json_decode($request->getContent(), true);

        // Setting User and Stopwatch to the Stopwatch
        if (!empty($requestData['activity'])) {
            $activity = $activityRepository->find($requestData['activity']);
            if (!$activity) {
                return new JsonResponse(['error' => 'Activity not found'], Response::HTTP_NOT_FOUND);
            }
            // Associate found User with the new Stopwatch
            $stopwatch->setActivity($activity);
        }

        // Validate the Stopwatch entity before flush
        $errors = $validator->validate($stopwatch);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Persist and flush new Stopwatch
        $em->persist($stopwatch);
        $em->flush();

        // Generate the "detail" URL for the new Stopwatch
        $location = $urlGenerator->generate('detail_stopwatch', ['id' => $stopwatch->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Serialize the new Stopwatch for the response
        $jsonStopwatch = $serializer->serialize($stopwatch, 'json', ['groups' => 'getStopwatch']);

        // return 201 with new Stopwatch and details URL
        return new JsonResponse($jsonStopwatch, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    /** PUT an existing stopwatch
     * {
     * "duration" : 4500
     * "stopwatch_id" : 27
     * }
     * 
     * @param Request $request
     * @param Stopwatch $currentStopwatch
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'update_stopwatch', methods: ['PUT'])]
    // #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de modification')]
    public function updateStopwatch(Request $request, Stopwatch $currentStopwatch, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        // Extracting stopwatch ID from the request content
        // $requestData = json_decode($request->getContent(), true);

        // Deserialization and Update Stopwatch without Stopwatch
        $serializer->deserialize($request->getContent(), Stopwatch::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentStopwatch]);


        $errors = $validator->validate($currentStopwatch);

        if ($errors->count() > 0) {
            // Srialize data
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


}
