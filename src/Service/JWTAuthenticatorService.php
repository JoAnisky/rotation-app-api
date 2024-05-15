<?php

namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTAuthenticatorService
{
    private $jwtEncoder;

    public function __construct(JWTEncoderInterface $jwtEncoder)
    {
        $this->jwtEncoder = $jwtEncoder;
    }

    public function authenticate(Request $request, array $requiredRoles = ['ROLE_ADMIN', 'ROLE_GAMEMASTER'])
    {
        // Récupérer le token JWT de l'en-tête Authorization
        $authorizationHeader = $request->headers->get('Authorization');
    
        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            throw new AuthenticationException('Token manquant ou invalide');
        }
    
        // Supprimer "Bearer " pour obtenir le token JWT
        $jwt = substr($authorizationHeader, 7);
    
        try {
            $payload = $this->jwtEncoder->decode($jwt);
    
            // Vérifier les rôles de l'utilisateur stockés dans le payload
            $userRoles = $payload['roles'];
            foreach ($requiredRoles as $role) {
                if (in_array($role, $userRoles)) {
                    return;
                }
            }
    
            throw new AccessDeniedException('Accès non autorisé pour ce rôle.');
        } catch (\Exception $e) {
            throw new AuthenticationException('Token invalide.');
        }
    }
}
