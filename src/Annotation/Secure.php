<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Secure
{
    public $roles;

    public function __construct(array $data)
    {
        $this->roles = $data['roles'];
    }
}