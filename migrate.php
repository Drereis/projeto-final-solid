<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Infra\TxtParkingRecordRepository;
use App\Infra\SqliteParkingRecordRepository;

$txtPath = __DIR__ . '/storage/parking_records.txt';
$sqlitePath = __DIR__ . '/storage/parking_records.db';

$txtRepo = new TxtParkingRecordRepository($txtPath);
$sqliteRepo = new SqliteParkingRecordRepository($sqlitePath);

$records = $txtRepo->findAll();

foreach ($records as $record) {
    $sqliteRepo->save($record);
}

echo "Migration completed. " . count($records) . " records migrated.\n";