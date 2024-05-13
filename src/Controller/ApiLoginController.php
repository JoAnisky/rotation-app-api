<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Json;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_login')]
    public function login(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();

        if(!$token){
            return new JsonResponse(['message' => 'token manquant'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        /** @var User $user */
        $user = $token->getUser();

        return $this->json([
            'username' => $user->getLogin(),
            'user_id' => $user->getId(),
            'role' => $user->getRoles(),
        ]);
    }
}
