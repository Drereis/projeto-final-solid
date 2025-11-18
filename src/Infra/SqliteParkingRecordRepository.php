<?php

declare(strict_types=1);

namespace App\Infra;

use App\Domain\Model\ParkingRecord;
use App\Domain\Model\Vehicle;
use App\Domain\Repository\ParkingRecordRepository;
use DateTimeImmutable;
use PDO;

class SqliteParkingRecordRepository implements ParkingRecordRepository
{
    private PDO $pdo;

    public function __construct(string $dbPath)
    {
        $this->pdo = new PDO("sqlite:{$dbPath}");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS parking_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                plate TEXT NOT NULL,
                type TEXT NOT NULL,
                entry_time TEXT NOT NULL,
                exit_time TEXT
            )
        ";
        $this->pdo->exec($sql);
    }

    public function save(ParkingRecord $record): void
    {
        $plate = $record->getVehicle()->getPlate();
        $stmt = $this->pdo->prepare("SELECT id FROM parking_records WHERE plate = ? AND exit_time IS NULL");
        $stmt->execute([$plate]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $updateStmt = $this->pdo->prepare("UPDATE parking_records SET exit_time = ? WHERE id = ?");
            $updateStmt->execute([
                $record->getExitTime() ? $record->getExitTime()->format(DateTimeImmutable::ATOM) : null,
                $existing['id']
            ]);
        } else {
            $insertStmt = $this->pdo->prepare("INSERT INTO parking_records (plate, type, entry_time, exit_time) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([
                $record->getVehicle()->getPlate(),
                $record->getVehicle()->getType(),
                $record->getEntryTime()->format(DateTimeImmutable::ATOM),
                $record->getExitTime() ? $record->getExitTime()->format(DateTimeImmutable::ATOM) : null
            ]);
        }
    }

    public function findActiveRecordByPlate(string $plate): ?ParkingRecord
    {
        $stmt = $this->pdo->prepare("SELECT * FROM parking_records WHERE plate = ? AND exit_time IS NULL ORDER BY id DESC LIMIT 1");
        $stmt->execute([$plate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->rowToRecord($row);
        }
        return null;
    }

    public function findLatestRecordByPlate(string $plate): ?ParkingRecord
    {
        $stmt = $this->pdo->prepare("SELECT * FROM parking_records WHERE plate = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$plate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->rowToRecord($row);
        }
        return null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM parking_records ORDER BY id");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->rowToRecord($row);
        }
        return $records;
    }

    private function rowToRecord(array $row): ParkingRecord
    {
        $vehicle = new Vehicle($row['plate'], $row['type']);
        $entryTime = new DateTimeImmutable($row['entry_time']);
        $record = new ParkingRecord($vehicle, $entryTime);
        if ($row['exit_time']) {
            $exitTime = new DateTimeImmutable($row['exit_time']);
            $record->markExit($exitTime);
        }
        return $record;
    }
}