<?php

declare(strict_types=1);

namespace App\Infra;

use App\Domain\Model\ParkingRecord;
use App\Domain\Model\Vehicle;
use App\Domain\Repository\ParkingRecordRepository;
use DateTimeImmutable;

class TxtParkingRecordRepository implements ParkingRecordRepository
{
    private string $filePath;
    private const DELIMITER = ',';

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        if (!file_exists($filePath)) {
            touch($filePath);
        }
    }


    private function loadData(): array
    {
        $records = [];
        $handle = fopen($this->filePath, 'r'); 
        
        if ($handle === false) {
            throw new \RuntimeException("Não foi possível abrir o arquivo: {$this->filePath}");
        }
        while (($data = fgetcsv($handle, 1000, self::DELIMITER)) !== false) {
            $records[] = $this->unserializeRecord($data);
        }
        fclose($handle);
        return $records;
    }

    private function saveData(array $records): void
    {
        $handle = fopen($this->filePath, 'w');

        if ($handle === false) {
            throw new \RuntimeException("Não foi possível escrever no arquivo: {$this->filePath}");
        }

        foreach ($records as $record) {
            fputcsv($handle, $this->serializeRecord($record), self::DELIMITER);
        }
        
        fclose($handle);
    }

    public function save(ParkingRecord $record): void
    {
        $records = $this->loadData();
        $plate = $record->getVehicle()->getPlate();
        $found = false;

        foreach ($records as $i => $existingRecord) {
            if ($existingRecord->getVehicle()->getPlate() === $plate && $existingRecord->getExitTime() === null) {
                $records[$i] = $record; 
                $found = true;
                break;
            }
        }

        if (!$found) {
            $records[] = $record;
        }

        $this->saveData($records);
    }

    public function findActiveRecordByPlate(string $plate): ?ParkingRecord
    {
        $records = $this->loadData();
        foreach ($records as $record) {
            if ($record->getVehicle()->getPlate() === $plate && $record->getExitTime() === null) {
                return $record;
            }
        }
        return null;
    }
    
    public function findAll(): array
    {
        return $this->loadData();
    }

    public function findLatestRecordByPlate(string $plate): ?ParkingRecord
    {
        return $this->findActiveRecordByPlate($plate);
    }

    private function serializeRecord(ParkingRecord $record): array
    {
        return [
            $record->getVehicle()->getPlate(),
            $record->getVehicle()->getType(),
            $record->getEntryTime()->format(DateTimeImmutable::ATOM),
            $record->getExitTime() ? $record->getExitTime()->format(DateTimeImmutable::ATOM) : '', // Salva string vazia se for nulo
        ];
    }
    private function unserializeRecord(array $data): ParkingRecord
    {

        $vehicle = new Vehicle($data[0], $data[1]);
        $entryTime = new DateTimeImmutable($data[2]);
        $exitTime = !empty($data[3]) ? new DateTimeImmutable($data[3]) : null;

        $record = new ParkingRecord($vehicle, $entryTime);
        if ($exitTime) {
            $record->markExit($exitTime);
        }
        return $record;
    }
}