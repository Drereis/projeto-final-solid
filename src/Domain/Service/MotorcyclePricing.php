<?php

declare(strict_types=1);

namespace App\Domain\Service;

class MotorcyclePricing implements PricingStrategy
{
    private const RATE = 3.00;
    private const TYPE = 'moto';

    public function getHourlyRate(): float
    {
        return self::RATE;
    }

    public function getVehicleType(): string
    {
        return self::TYPE;
    }
}