<?php 

declare(strict_types=1);

namespace App\Application\Service;  

use App\Domain\Model\Vehicle;
use App\Domain\Model\ParkingRecord;
use App\Domain\Repository\ParkingRecordRepository;
use App\Domain\Service\PricingService;
use App\Domain\Validator\VehicleValidator;
use DateTimeImmutable;

class ParkingControlService
{
    private ParkingRecordRepository $repository;
    private PricingService $pricingService;

    public function __construct(ParkingRecordRepository $repository, PricingService $pricingService)
    {
       $this->repository = $repository;
       $this->pricingService = $pricingService;
    }

    public function checkIn(string $plate, string $type): ParkingRecord
    {

        $plate =  strtoupper(trim($plate));
        $type = strtolower(trim($type));

        if (!VehicleValidator::isValidPlate($plate)) {
            throw new \DomainException(message: "Formato de placa inválido. Use o padrão Mercosul (LLLNLNN). EX: ABC1D34");
        }

        if (!VehicleValidator::isValidType($type)) {
            throw new \DomainException(message: "Tipo de veiculo inválido. Use 'carro', 'moto' ou 'caminhao'.");
        }

        $activeRecord = $this->repository->findActiveRecordByPlate($plate);
        if ($activeRecord !== null) {
            throw new \DomainException(message: "Veiculo com placa $plate já está no pátio.");
        }

        $vehicle = new Vehicle($plate, $type);
        $record = new ParkingRecord($vehicle, new DateTimeImmutable());
        $this->repository->save($record);
        return $record;
    }

    public function checkout(string $plate): array
    {

        $plate = strtoupper(trim($plate));
        $record = $this->repository->findActiveRecordByPlate($plate);

        if ($record === null) {
            throw new \DomainException("Veiculo com placa $plate não encontrado no patio.");
        }

        $record->markExit(new DateTimeImmutable());

        $totalHours = $this->pricingService->calculateTotalHours($record);
        $cost = $this->pricingService->calculateCost($record, $totalHours);
        $this->repository->save($record);

        return [
            'record' => $record,
            'totalHours' => $totalHours,
            'cost' => $cost,
        ];
    }

    public function generateReport(): array
    {
        $allRecords = $this->repository->findAll();
        $report = [
            'totalFaturamento' => 0,
            'totalVeiculos' => 0,
            'detalhesPorTipo' => [
                'carro' => ['faturamento' => 0, 'veiculos' => 0],
                'moto' => ['faturamento' => 0, 'veiculos' => 0],
                'caminhao' => ['faturamento' => 0, 'veiculos' => 0],
            ],
        ];

        foreach ($allRecords as $record) {
            if ($record->getExitTime() === null) {
                continue;
            }

            $type = $record->getVehicle()->getType();
            $hours = $this->pricingService->calculateTotalHours($record);
            $cost = $this->pricingService->calculateCost($record, $hours);

            $report['totalFaturamento'] += $cost;
            $report['totalVeiculos'] ++;

            if(isset($report['detalhesPorTipo'][$type])) {
                $report['detalhesPorTipo'][$type]['faturamento'] += $cost;
                $report['detalhesPorTipo'][$type]['veiculos'] ++;
            }
        }

        return $report;
    }
}