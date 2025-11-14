<?php

declare(strict_types=1);

namespace App\Domain\Service;

interface PricingStrategy
{
    public function getHourlyRate(): float;
    public function getVehicleType(): string;
}

