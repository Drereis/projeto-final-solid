<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\ParkingControlService;
use App\Domain\Service\CarPricing;
use App\Domain\Service\MotorcyclePricing;
use App\Domain\Service\PricingService;
use App\Domain\Service\TruckPricing;
use App\Infra\SqliteParkingRecordRepository;

$errorMessage = null;
$billingReport = null;
$activeVehicles = [];

$dbPath = __DIR__ . '/../storage/parking_records.db';

try {
    $repository = new SqliteParkingRecordRepository($dbPath);
    $pricingStrategies = [
        new CarPricing(),
        new MotorcyclePricing(),
        new TruckPricing(),
    ];

    $pricingService = new PricingService($pricingStrategies);
    $parkingService = new ParkingControlService($repository, $pricingService);

    $billingReport = $parkingService->generateReport();
    $allRecords = $repository->findAll();
    $activeVehicles = array_filter($allRecords, fn($record) => $record->getExitTime() === null);

} catch (\DomainException $e) {
    $errorMessage = $e->getMessage();
} catch (\Exception $e) {
    $errorMessage = "Ocorreu um erro inesperado: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Estacionamento SOLID</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans leading-relaxed p-5 max-w-7xl mx-auto bg-gray-100">
    <a href="index.php" class="inline-block px-5 py-3 bg-blue-600 text-white no-underline rounded mb-5 hover:bg-blue-700">← Voltar para Operações</a>
    <h1 class="text-gray-800 mb-5">Relatórios de Uso e Faturamento</h1>

    <?php if ($errorMessage): ?>
        <p class="text-red-600 font-bold p-3 bg-red-100 rounded mb-5"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <div class="flex flex-col gap-5">
        <div class="bg-white p-5 rounded-lg shadow">
            <h2 class="text-gray-800 mb-4">Relatório de Faturamento</h2>
            <?php if ($billingReport): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div class="bg-gray-50 p-4 rounded border-l-4 border-blue-600">
                        <p class="text-gray-600 text-sm mb-1">Total Faturado</p>
                        <p class="text-2xl font-bold text-gray-800">R$ <?= number_format($billingReport['totalFaturamento'], 2, ',', '.') ?></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded border-l-4 border-blue-600">
                        <p class="text-gray-600 text-sm mb-1">Veículos Processados</p>
                        <p class="text-2xl font-bold text-gray-800"><?= $billingReport['totalVeiculos'] ?></p>
                    </div>
                </div>

                <h3 class="text-gray-700 text-lg mb-3">Faturamento por Tipo</h3>
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50 font-bold">Tipo de Veículo</th>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50 font-bold">Quantidade</th>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50 font-bold">Faturamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($billingReport['detalhesPorTipo'] as $tipo => $data): ?>
                        <?php if ($data['veiculos'] > 0): ?>
                        <tr>
                            <td class="border border-gray-300 p-2 text-left"><?= ucfirst($tipo) ?></td>
                            <td class="border border-gray-300 p-2 text-left"><?= $data['veiculos'] ?></td>
                            <td class="border border-gray-300 p-2 text-left">R$ <?= number_format($data['faturamento'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-500 italic p-5 text-center">Nenhum dado de faturamento disponível.</p>
            <?php endif; ?>
        </div>

        <div class="bg-white p-5 rounded-lg shadow">
            <h2 class="text-gray-800 mb-4">Veículos Estacionados</h2>
            <p class="text-gray-600 mb-3">Total de veículos ativos: <span class="font-bold text-lg"><?= count($activeVehicles) ?></span></p>
            <?php if (count($activeVehicles) > 0): ?>
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50 font-bold">Placa</th>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50 font-bold">Tipo</th>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50 font-bold">Entrada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeVehicles as $record): ?>
                        <tr>
                            <td class="border border-gray-300 p-2 text-left"><?= htmlspecialchars($record->getVehicle()->getPlate()) ?></td>
                            <td class="border border-gray-300 p-2 text-left"><?= ucfirst($record->getVehicle()->getType()) ?></td>
                            <td class="border border-gray-300 p-2 text-left"><?= $record->getEntryTime()->format('d/m/Y H:i') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-500 italic p-5 text-center">Nenhum veículo estacionado no momento.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

