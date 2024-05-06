<?php

namespace App\Service;

use App\Repository\ActivityRepository;

class CodeGeneratorService
{
    private $activityRepository;

    public function __construct(ActivityRepository $activityRepository)
    {
        $this->activityRepository = $activityRepository;
    }

    public function generateUniqueCode(string $type): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while ($this->isCodeExists($code, $type));

        return $code;
    }

    private function isCodeExists(string $code, string $type): bool
    {
        // Supposons que vous avez une méthode dans votre repository pour vérifier l'existence du code
        // Vous devez implémenter cette méthode dans votre ActivityRepository
        return $this->activityRepository->codeExists($code, $type);
    }
}
