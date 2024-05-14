<?php

namespace App\EventListener;

use App\Annotation\Secure;
use App\Service\JWTAuthenticatorService;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class SecurityListener
{
    private $reader;
    private $jwtAuthenticator;

    public function __construct(Reader $reader, JWTAuthenticatorService $jwtAuthenticator)
    {
        $this->reader = $reader;
        $this->jwtAuthenticator = $jwtAuthenticator;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (!is_array($controller)) {
            return;
        }

        // Vérifiez l'annotation sur la méthode
        $method = new \ReflectionMethod($controller[0], $controller[1]);
        $class = new \ReflectionClass($controller[0]);

        $annotation = $this->reader->getMethodAnnotation($method, Secure::class) ??
                      $this->reader->getClassAnnotation($class, Secure::class);

        if ($annotation) {
            try {
                $this->jwtAuthenticator->authenticate($request, $annotation->roles);
            } catch (AccessDeniedHttpException $e) {
                throw new AccessDeniedHttpException($e->getMessage());
            } catch (UnauthorizedHttpException $e) {
                throw new UnauthorizedHttpException($e->getMessage());
            }
        }
    }
}
