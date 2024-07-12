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

        // Récupérer le token JWT des en-têtes ou des cookies
        $jwt = $request->headers->get('Authorization');
        if ($jwt && str_starts_with($jwt, 'Bearer ')) {
            $jwt = substr($jwt, 7); // Supprimer "Bearer " pour obtenir le token JWT
        } else {
            $jwt = $request->cookies->get('access_token');
        }

        $csrfToken = bin2hex(random_bytes(32)); // Generate random CSRF token

        /** @var User $user */
        $user = $token->getUser();

        $tokenData = [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'csrf' => $csrfToken,
        ];

        $token = $JWTManager->createFromPayload($user, $tokenData);

        $response = new JsonResponse([
            'csrfToken' => $csrfToken, // Send CSRF Token with response
            'username' => $user->getLogin(),
            'user_id' => $user->getId(),
            'role' => $user->getRoles(),
        ]);
        $response->headers->setCookie(new Cookie('access_token', $token, 0, '/', null, true, true, false, 'Strict'));

        return $response;
    }
}
