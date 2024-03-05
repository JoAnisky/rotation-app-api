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
use Symfony\Component\Security\Http\Attribute\IsGranted;
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

    #[Route('/details/{id}', name: 'detail_animator', methods: ['GET'])]
    public function getDetailAnimator(Animator $animator, SerializerInterface $serializer): JsonResponse
    {
        // If animator doesn't ParamConverter will throw an Exception

        // Turn $animator object into JSON format
        $jsonAnimator = $serializer->serialize($animator, 'json', ['groups' => 'getAnimators']);
        return new JsonResponse($jsonAnimator, Response::HTTP_OK, [], true);
    }
    #[Route('/delete/{id}', name: 'delete_animator', methods: ['DELETE'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de suppression')]
    public function deleteAnimator(Animator $animator, EntityManagerInterface $em): JsonResponse
    {

        $em->remove($animator);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create an Animator using the following structure 
     * 
     * {
     *  "name" : "animatorName",
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
    #[Route('/create', name: 'create_animator', methods: ['POST'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de création')]
    public function createAnimator(Request $request, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {        
        // Create new Animator object with data provided
        $animator = $serializer->deserialize($request->getContent(), Animator::class, 'json');
        // Extracting user ID from the request content
        $requestData = json_decode($request->getContent(), true);

        // Setting User and Activity to the Team
        if (!empty($requestData['user'])) {
            $user = $userRepository->find($requestData['user']);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            // Associate found User with the new Team
            $animator->setUser($user);
        }
    
        // Check if no validation error, if errors -> returns error 400
        $errors = $validator->validate($animator);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
    
        // Persist and flush new Animator
        $em->persist($animator);
        $em->flush();
    
        // Générerate URL "détails" for the new animator
        $location = $urlGenerator->generate('detail_animator', ['id' => $animator->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Sérialize new Animator for the the response
        $jsonAnimator = $serializer->serialize($animator, 'json', ['groups' => 'getAnimators']);
    
        // return 201 with new Animator and details URL
        return new JsonResponse($jsonAnimator, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * PUT an existing Animator
     * {
     *  "name" : "newName"
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
    #[Route('/update/{id}', name: 'update_animator', methods: ['PUT'])]
    #[IsGranted('ROLE_GAMEMASTER', message: 'Vous n\'avez pas les droits de modification')]
    public function updateAnimator(Request $request, Animator $currentAnimator, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {

        /* OBJECT_TO_POPULATE option update an existing animator's properties are updated with the new data, rather than creating a new object */
        $updatedAnimator = $serializer->deserialize(
            $request->getContent(),
            Animator::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAnimator]
        );

        /* The updated animator object is validated using the ValidatorInterface to ensure the data is valid. If any errors are found respond a HTTP_BAD_REQUEST status*/
        $errors = $validator->validate($updatedAnimator);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();

        // HTTP_NO_CONTENT status code 204 is returned without content
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
