<?php

namespace App\Controller;

use App\Entity\Animator;
use App\Repository\AnimatorRepository;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/animators')]
class AnimatorController extends AbstractController
{
    #[Route('/', name: 'animators', methods: ['GET'])]
    public function getAnimatorsList(AnimatorRepository $animatorRepository, SerializerInterface $serializer): JsonResponse
    {
        $animatorsList = $animatorRepository->findAll();
        $jsonAnimatorsList = $serializer->serialize($animatorsList, 'json', ['groups' => 'getAnimators']);
        return new JsonResponse($jsonAnimatorsList, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'detail_animator', methods: ['GET'])]
    public function getDetailAnimator(Animator $animator, SerializerInterface $serializer): JsonResponse
    {
        // If animator doesn't ParamConverter will throw an Exception

        // Turn $animator object into JSON format
        $jsonAnimator = $serializer->serialize($animator, 'json', ['groups' => 'getAnimators']);
        return new JsonResponse($jsonAnimator, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'delete_animator', methods: ['DELETE'])]
    public function deleteAnimator(Animator $animator, EntityManagerInterface $em): JsonResponse
    {

        $em->remove($animator);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/', name: 'create_animator', methods: ['POST'])]
    public function createAnimator(Request $request, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        // Extracting user ID from the request content
        $requestData = json_decode($request->getContent(), true);
        $userId = $requestData['user'];
    
        // check if associated User exists
        $user = $userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    
        /* The request body is deserialized into an existing Animator object ($currentAnimator) using the OBJECT_TO_POPULATE option.
        * This ensures that the existing animator's properties are updated with the new data, rather than creating a new object
        */
        $animator = $serializer->deserialize($request->getContent(), Animator::class, 'json');
    
        // Associate found User with the new Animator
        $animator->setUser($user);
    
        // Check if no validation error, if errors -> returns error 400
        $errors = $validator->validate($animator);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
    
        // Persist and flush new Animator
        $em->persist($animator);
        $em->flush();
    
        // Sérialize new Animator for the response
        $jsonAnimator = $serializer->serialize($animator, 'json', ['groups' => 'getAnimators']);
    
        // Générerate URL "détails" for the new animator
        $location = $urlGenerator->generate('detail_animator', ['id' => $animator->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // return 201 with new Animator and details URL
        return new JsonResponse($jsonAnimator, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * PUT an existing animator
     * {
     *  "name" : "newLogin",
     *  "user" : 1
     * }
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param Animator $currentAnimator
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'update_animator', methods: ['PUT'])]
    public function updateAnimator(Request $request, UserRepository $userRepository, Animator $currentAnimator, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        // Extracting user ID from the request content
        $requestData = json_decode($request->getContent(), true);
        $userId = $requestData['user'];

        $user = $userRepository->find($userId);

        // Check if the user was found
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        /* The request body is deserialized into an existing Animator object ($currentAnimator) using the OBJECT_TO_POPULATE option.
        * This ensures that the existing animator's properties are updated with the new data, rather than creating a new object
        */
        $updatedAnimator = $serializer->deserialize(
            $request->getContent(),
            Animator::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAnimator]
        );
        // Associate found User with the new Animator
        $currentAnimator->setUser($user);
        /* The updated animator object is validated using the ValidatorInterface to ensure the data is valid.
        *   If any errors are found, a JSON response with the errors is returned with a HTTP_BAD_REQUEST status
        */
        $errors = $validator->validate($updatedAnimator);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // The updated animator object is persisted to the database using EntityManagerInterface
        $em->persist($updatedAnimator);
        $em->flush();

        // A JsonResponse with a HTTP_NO_CONTENT status code 204 is returned, indicating successful update without any content in the response body.
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
