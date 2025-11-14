<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\ParkingRecord;
use App\Domain\Model\Vehicle;

interface ParkingRecordRepository
{
    public function save(ParkingRecord $record): void;
    public function findActiveRecordByPlate(string $plate): ?ParkingRecord;
    public function findLatestRecordByPlate(string $plate): ?ParkingRecord;
    public function findAll(): array;
}