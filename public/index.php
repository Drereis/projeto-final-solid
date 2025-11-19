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
$successMessage = null;


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


    $action = $_POST['action'] ?? null;

    if ($action === 'check-in') {
        $plate = $_POST['plate'] ?? '';
        $type = $_POST['type'] ?? '';
        
        $parkingService->checkIn($plate, $type);
        $successMessage = "Veículo $plate ($type) deu entrada com sucesso.";
    
    } elseif ($action === 'check-out') {
        $plate = $_POST['plate'] ?? '';
        
        $result = $parkingService->checkOut($plate);
        $cost = number_format($result['cost'], 2, ',', '.');
        $successMessage = "Veículo $plate deu saída. Tempo: {$result['totalHours']}h. Custo: R$ $cost";
    }
    $report = $parkingService->generateReport();

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
    <title>Estacionamento SOLID</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-5 max-w-4xl mx-auto font-sans leading-relaxed">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Controle de Estacionamento Inteligente (SOLID)</h1>

    <?php if ($successMessage): ?>
        <p class="text-green-600 font-bold mb-4"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p class="text-red-600 font-bold mb-4"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row gap-8">
        <div class="bg-white p-5 rounded-lg shadow flex-1">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Operações</h2>
            
            <h3 class="text-lg font-medium text-gray-700 mb-3">Check-in</h3>
            <form action="index.php" method="POST" class="flex flex-col gap-3">
                <input type="hidden" name="action" value="check-in">
                <label class="flex flex-col gap-1">
                    <span class="text-gray-700">Placa:</span>
                    <input type="text" name="plate" required class="p-2.5 border border-gray-300 rounded">
                </label>
                
                <label class="flex flex-col gap-1">
                    <span class="text-gray-700">Tipo:</span>
                    <input type="text" name="type" placeholder="carro, moto, caminhao" required class="p-2.5 border border-gray-300 rounded">
                </label>
                
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 cursor-pointer transition">Registrar Entrada</button>
            </form>

            <hr class="border-t border-gray-300 my-6">

            <h3 class="text-lg font-medium text-gray-700 mb-3">Check-out</h3>
            <form action="index.php" method="POST" class="flex flex-col gap-3">
                <input type="hidden" name="action" value="check-out">
                <label class="flex flex-col gap-1">
                    <span class="text-gray-700">Placa:</span>
                    <input type="text" name="plate" required class="p-2.5 border border-gray-300 rounded">
                </label>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 cursor-pointer transition">Registrar Saída</button>
            </form>
        </div>

        <div class="bg-white p-5 rounded-lg shadow flex-1">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Relatório de Faturamento</h2>
            <a href="report.php" class="inline-block px-5 py-2.5 bg-green-600 text-white rounded mb-4 hover:bg-green-700 transition">Ver Relatórios Completos →</a>
            <?php if (isset($report)): ?>
                <h3 class="text-lg font-medium text-gray-700 mb-2">Total Faturado: R$ <?= number_format($report['totalFaturamento'], 2, ',', '.') ?></h3>
                <p class="text-gray-600 mb-4">Total de Veículos Processados: <?= $report['totalVeiculos'] ?></p>

                <table class="w-full border-collapse mt-3">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50">Tipo de Veículo</th>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50">Veículos</th>
                            <th class="border border-gray-300 p-2 text-left bg-gray-50">Faturamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['detalhesPorTipo'] as $tipo => $data): ?>
                        <tr>
                            <td class="border border-gray-300 p-2"><?= ($tipo) ?></td>
                            <td class="border border-gray-300 p-2"><?= $data['veiculos'] ?></td>
                            <td class="border border-gray-300 p-2">R$ <?= number_format($data['faturamento']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>