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
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 900px; margin: auto; background-color: #f4f4f4; }
        h1, h2, h3 { color: #333; }
        hr { border: 1px solid #ddd; }
        .container { display: flex; gap: 30px; }
        .form-section, .report-section { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; }
        form { display: flex; flex-direction: column; gap: 10px; }
        input, select, button { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #007bff; color: white; cursor: pointer; border: none; }
        button:hover { background-color: #0056b3; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Controle de Estacionamento Inteligente (SOLID)</h1>

    <?php if ($successMessage): ?>
        <p class="success"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <div class="container">
        <div class="form-section">
            <h2>Operações</h2>
            
            <h3>Check-in</h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="check-in">
                <label>Placa: <input type="text" name="plate" required></label>
                
                <label>Tipo: <input type="text" name="type" placeholder="carro, moto, caminhao" required></label>
                
                <button type="submit">Registrar Entrada</button>
            </form>

            <hr>

            <h3>Check-out</h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="check-out">
                <label>Placa: <input type="text" name="plate" required></label>
                <button type="submit">Registrar Saída</button>
            </form>
        </div>

        <div class="report-section">
            <h2>Relatório de Faturamento</h2>
            <?php if (isset($report)): ?>
                <h3>Total Faturado: R$ <?= number_format($report['totalFaturamento'], 2, ',', '.') ?></h3>
                <p>Total de Veículos Processados: <?= $report['totalVeiculos'] ?></p>

                <table>
                    <thead>
                        <tr>
                            <th>Tipo de Veículo</th>
                            <th>Veículos</th>
                            <th>Faturamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['detalhesPorTipo'] as $tipo => $data): ?>
                        <tr>
                            <td><?= ucfirst($tipo) ?></td>
                            <td><?= $data['veiculos'] ?></td>
                            <td>R$ <?= number_format($data['faturamento'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>