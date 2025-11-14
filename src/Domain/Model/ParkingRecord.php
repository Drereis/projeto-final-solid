<?php

declare(strict_types=1);

namespace App\Domain\Model;

use DateTimeImmutable;

class ParkingRecord 
{
    private Vehicle $vehicle;
    private DateTimeImmutable $entryTime;
    private ?DateTimeImmutable $exitTime = null;

    public function __construct(Vehicle $vehicle, DateTimeImmutable $entryTime)
    {
        $this->vehicle = $vehicle;
        $this->entryTime = $entryTime;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    public function getEntryTime(): DateTimeImmutable
    {
        return $this->entryTime; 
    }

    public function getExitTime(): ?DateTimeImmutable
    {
        return $this->exitTime;
    }

    public function markExit(DateTimeImmutable $exitTime): void
    {
        if ($this->exitTime !==null) {
            return;
        }
        $this->exitTime = $exitTime;
    }
}