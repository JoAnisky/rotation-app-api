<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Repository\StandRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\ActivityService;
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

#[Route('/activities')]
class ActivityController extends AbstractController
{
    #[Route('/', name: 'activities', methods: ['GET'])]
    public function getActivitiesList(ActivityRepository $activityRepository, SerializerInterface $serializer): JsonResponse
    {
        $activitiesList = $activityRepository->findAll();
        $jsonActivitiesList = $serializer->serialize($activitiesList, 'json', ['groups' => 'getActivity']);
        return new JsonResponse($jsonActivitiesList, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'detail_activity', methods: ['GET'])]
    public function getDetailActivity(Activity $activity, SerializerInterface $serializer): JsonResponse
    {
        // If no activity ParamConverter will throw an Exception
        // Turn $activity object into JSON format
        $jsonActivity = $serializer->serialize($activity, 'json', ['groups' => 'getActivity']);
        return new JsonResponse($jsonActivity, Response::HTTP_OK, [], true);
    }

    /**
     * @param Activity $activity
     * @param ActivityService $activityService
     * @param StandRepository $standRepository
     * @param TeamRepository $teamRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete_activity', methods: ['DELETE'])]
    public function deleteActivity(Activity $activity, ActivityService $activityService, StandRepository $standRepository, TeamRepository $teamRepository, EntityManagerInterface $em): JsonResponse
    {
        $em->getConnection()->beginTransaction(); // Start transaction

        try {
            /* Before deleting an Activity, check if there is an activity_id in "Stand" and "Team" tables
            In this case, set activity_id to null
            (Avoid SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row) */
            $activityService->nullifyActivityRelations($standRepository->findBy(['activity' => $activity]), $em);
            $activityService->nullifyActivityRelations($teamRepository->findBy(['activity' => $activity]), $em);

            $em->remove($activity);
            $em->flush(); // Persist changes

            $em->getConnection()->commit(); // Commit transaction

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            $em->getConnection()->rollBack(); // Roll back on error

            // Return a JsonResponse indicating an error
            return new JsonResponse([
                'error' => 'An unexpected error occurred.',
                'message' => $e->getMessage() // Optionally include the exception message for debugging
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a Activity using the following structure 
     * 
     * {
     *  "name" : "activityName",
     *  "user" : 10 (needed for the moment, see AuthenticationService)
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
    #[Route('/', name: 'create_activity', methods: ['POST'])]
    public function createActivity(Request $request, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        // Create new activity object with data provided
        $activity = $serializer->deserialize($request->getContent(), Activity::class, 'json');

        // Decode request content
        $requestData = json_decode($request->getContent(), true);

        // Setting User and Activity to the Activity
        if (!empty($requestData['user'])) {
            $user = $userRepository->find($requestData['user']);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            // Associate found User with the new Activity
            $activity->setUser($user);
        }

        // Validate the Activity entity before flush
        $errors = $validator->validate($activity);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Persist and flush new Activity
        $em->persist($activity);
        $em->flush();

        // Generate the "detail" URL for the new Activity
        $location = $urlGenerator->generate('detail_activity', ['id' => $activity->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Serialize the new Activity for the response
        $jsonActivity = $serializer->serialize($activity, 'json', ['groups' => 'getActivity']);

        // return 201 with new Activity and details URL
        return new JsonResponse($jsonActivity, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    /** PUT an existing activity
     * {
     * "name" : "newLogin",
     * "activity_id" : 27
     * }
     * 
     * @param Request $request
     * @param Activity $currentActivity
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */ 
    #[Route('/{id}', name: 'update_activity', methods: ['PUT'])] 
    public function updateActivity(Request $request,  Activity $currentActivity, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        // // Extracting activity ID from the request content
        // $requestData = json_decode($request->getContent(), true);

        // Deserialization and Update Activity without Activity
        $serializer->deserialize($request->getContent(),Activity::class,'json',[AbstractNormalizer::OBJECT_TO_POPULATE => $currentActivity]);

        /* The updated activity object is validated using the ValidatorInterface to ensure the data is valid.
        *  If any errors are found, a JSON response with the errors is returned with a HTTP_BAD_REQUEST status*/
        $errors = $validator->validate($currentActivity);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();
        // A JsonResponse with a HTTP_NO_CONTENT status code 204, indicating successful update without any content in the response body.
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
