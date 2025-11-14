<?php

declare(strict_types=1);

namespace App\Domain\Service;

class CarPricing implements PricingStrategy
{
    private const RATE = 5.00;
    private const TYPE = 'carro';

    public function getHourlyRate(): float
    {
        return self::RATE;
    }

    public function getVehicleType(): string
    {
        return self::TYPE;
    }
}