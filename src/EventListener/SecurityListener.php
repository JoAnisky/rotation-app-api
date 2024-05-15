<?php
namespace App\EventListener;

use App\Annotation\Secure;
use App\Service\JWTAuthenticatorService;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use ReflectionClass;
use ReflectionMethod;

class SecurityListener 
{
    private $jwtAuthenticator;

    public function __construct(JWTAuthenticatorService $jwtAuthenticator)
    {
        $this->jwtAuthenticator = $jwtAuthenticator;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (!is_array($controller)) {
            return;
        }

        $method = new ReflectionMethod($controller[0], $controller[1]);
        $class = new ReflectionClass($controller[0]);

        $annotation = $this->getSecureAnnotation($method) ?? $this->getSecureAnnotation($class);

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

    private function getSecureAnnotation($reflection)
    {
        $attributes = $reflection->getAttributes(Secure::class);
        if (count($attributes) > 0) {
            return $attributes[0]->newInstance();
        }
        return null;
    }
}

