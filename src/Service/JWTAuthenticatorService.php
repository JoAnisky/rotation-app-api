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
        $cookies = $request->cookies;

        if ($cookies->has('authToken')) {
            $cookieToken = $cookies->get('authToken');

            try {
                $payload = $this->jwtEncoder->decode($cookieToken);

                // Vérifiez les rôles ou autres informations du payload
                $userRoles = $payload['roles'];
                foreach ($requiredRoles as $role) {
                    if (in_array($role, $userRoles)) {
                        return;
                    }
                }

                throw new AccessDeniedException('Access denied for this user role.');
            } catch (\Exception $e) {
                throw new AuthenticationException('Depuis le JWT Service : Invalid token.');
            }
        } else {
            throw new AuthenticationException('No authToken found in cookies.');
        }
    }
}
