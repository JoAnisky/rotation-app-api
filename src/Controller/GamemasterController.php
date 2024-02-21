<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Only users with the ROLE_ADMIN permission can access this controller.
 */
#[Route('/gamemasters')]
#[IsGranted('ROLE_ADMIN', message:'Vous n\'avez pas les droits pour accèder à cette section')]
class GamemasterController extends AbstractController
{
    private $gamemasterPasswordHasher;

    /**
     * @param UserPasswordHasherInterface $gamemasterPasswordHasher - used for User password encryption
     */
    public function __construct(UserPasswordHasherInterface $gamemasterPasswordHasher)
    {
        $this->gamemasterPasswordHasher = $gamemasterPasswordHasher;
    }

    /**
     * Retrieves all gamemasters based on their role
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/all', name: 'gamemaster', methods: ['GET'])]
    public function getGamemasters(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $gamemasterList = $userRepository->getUsersByRole("ROLE_GAMEMASTER");

        if (empty($gamemasterList)) {
            // If no data found, return 404
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        $jsonUsersList = $serializer->serialize($gamemasterList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }

    /**
     * Retrieves one gamemaster based on his role and ID.
     * @param int $gamemasterId - Gamemaster id
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/details/{gamemasterId}', name: 'detail_gamemaster', methods: ['GET'])]
    public function getOneGamemaster(int $gamemasterId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $gamemaster = $userRepository->getUserByRoleAndId("ROLE_GAMEMASTER", $gamemasterId);

        if (empty($gamemaster)) {
            // If no data found, return 404
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Turn $gamemaster object into JSON format
        $jsonGamemaster = $serializer->serialize($gamemaster, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonGamemaster, Response::HTTP_OK, [], true);
    }

    /**
     * Delete one gamemaster by ID.
     * @param User $gamemaster
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/delete/{id}', name: 'delete_gamemaster', methods: ['DELETE'])]
    public function deleteGamemaster(User $user, EntityManagerInterface $em): JsonResponse
    {
        // Check if the user has the "gamemaster" role
        if (!in_array('ROLE_GAMEMASTER', $user->getRoles())) {
            // If user is not a gamemaster, return error 403
            return new JsonResponse(['error' => 'User is not a gamemaster'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create an Gamemaster using the following structure 
     * 
     * {
     *  "login" : "login",
     *  "password" : "password",
     * }
     * Use the UserPasswordHasherInterface to hash the password
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/create', name: 'create_gamemaster', methods: ['POST'])]
    public function createGamemaster(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        // Deserialize $request->getcontent
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // Check if user data is conform
        $errors = $validator->validate($user);

        // If validation error, ERROR 400 is returned
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Data is ok
        // Hash the password before persisting
        $user->setPassword($this->gamemasterPasswordHasher->hashPassword($user, $user->getPassword()));
        // Set gamemaster Role
        $user->setRoles(["ROLE_GAMEMASTER"]);
        // Persist and upgrade user
        $em->persist($user);
        $em->flush();

        // Serialize new User Object for response
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        // Decode the JSON to pass an array to JsonResponse
        $jsonUserData = json_decode($jsonUser, true);

        $location = $urlGenerator->generate('detail_gamemaster', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUserData, Response::HTTP_CREATED, ["Location" => $location]);
    }

    /**
     * PUT an existing Gamemaster
     * {
     *  "login" : "newLogin",
     *  "password" : "newPassword",
     * }
     * 
     * @param Request $request
     * @param User $currentUser
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/update/{id}', name: 'update_gamemaster', methods: ['PUT'])]
    public function updateGamemaster(Request $request, User $currentUser, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        // Check if the user has the "gamemaster" role
        if (!in_array('ROLE_GAMEMASTER', $currentUser->getRoles())) {
            // If user is not a gamemaster, return error 403
            return new JsonResponse(['error' => 'User is not a gamemaster'], Response::HTTP_FORBIDDEN);
        }

        /* The request body is deserialized into an existing User object ($currentUser) using the OBJECT_TO_POPULATE option.
        * This ensures that the existing user's properties are updated with the new data, rather than creating a new object
        */
        $updatedGamemaster = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );

        /* The updated user object is validated using the ValidatorInterface to ensure the data is valid.
        *   If any errors are found, a JSON response with the errors is returned with a HTTP_BAD_REQUEST status
        */
        $errors = $validator->validate($updatedGamemaster);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Check if password must change
        $content = $request->toArray();
        // Get new password or null if nothing is provided
        $newPassword = $content['password'] ?? null;

        if (!empty($newPassword)) {
            // Hash the new password before persisting
            $currentUser->setPassword($this->gamemasterPasswordHasher->hashPassword($currentUser, $newPassword));
        }

        $em->flush();

        // A JsonResponse with a HTTP_NO_CONTENT status code 204 is returned, indicating successful update without any content in the response body.
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
