<?php

declare(strict_types=1);

namespace App\Domain\Validator;

class VehicleValidator
{
    public static function isValidPlate(string $plate): bool
    {
        $regex = '/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/';

        return preg_match($regex, $plate) === 1;
    }

    public static function isValidType(string $type): bool
    {
        $allowedTypes = ['carro', 'moto', 'caminhao'];
        return in_array($type, $allowedTypes);
    }
}