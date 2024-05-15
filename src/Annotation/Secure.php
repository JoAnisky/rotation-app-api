<?php

namespace App\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Secure
{
    public array $roles;

    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }
}