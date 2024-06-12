<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_login')]
    public function login(Request $request, TokenStorageInterface $tokenStorage, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        $token = $tokenStorage->getToken();

        // Récupérer le token JWT de la requête
        $jwt = $request->headers->get('Authorization');

        if (!$jwt || !str_starts_with($jwt, 'Bearer ')) {
            return new JsonResponse(['message' => 'Token manquant ou invalide'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $jwt = substr($jwt, 7); // Supprimer "Bearer " pour obtenir le token JWT

        $csrfToken = bin2hex(random_bytes(32)); // Générer un token CSRF aléatoire

        /** @var User $user */
        $user = $token->getUser();

        $tokenData = [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'csrf' => $csrfToken, // Ajouter le token CSRF au payload
        ];

        $token = $JWTManager->createFromPayload($user, $tokenData);

        $response = new JsonResponse([ // Construire la réponse avec le token CSRF
            'csrfToken' => $csrfToken, // Token CSRF envoyé dans la réponse
            'username' => $user->getLogin(),
            'user_id' => $user->getId(),
            'role' => $user->getRoles(),
        ]);
        $response->headers->setCookie(new Cookie('access_token', $token, 0, '/', null, true, true, false, 'Strict'));

        return $response;
    }
}
