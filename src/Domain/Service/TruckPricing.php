<?php

declare(strict_types=1);

namespace App\Domain\Service;

class TruckPricing implements PricingStrategy
{
    private const RATE = 10.00;
    private const TYPE = 'caminhao';

    public function getHourlyRate(): float
    {
        return self::RATE;
    }

    public function getVehicleType(): string
    {
        return self::TYPE;
    }
}