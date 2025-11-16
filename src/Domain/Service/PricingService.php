<?php 

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Model\ParkingRecord;
use DateInterval;

class PricingService
{
    private array $pricingStrategies;
    public function __construct(array $pricingStrategies)
    {
        $this->pricingStrategies = $this->mapStrategies($pricingStrategies);
    }

    private function mapStrategies(array $strategies): array
    {
        $map = [];
        foreach($strategies as $strategy) {
            $map[$strategy->getVehicleType()] = $strategy;
        }
        return $map;
    }

    public function calculateTotalHours(ParkingRecord $record): int
    {
        $entryTime = $record->getEntryTime();
        $exitTime = $record->getExitTime();

        if ($exitTime === null) {
            throw new \DomainException(message: "O registro de estacionamento ainda está ativo(veiculo sem horário de saida).");
        }

        $interval = $entryTime->diff($exitTime);

        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        $totalHours = $totalMinutes / 60.0;

        return (int) ceil($totalHours);
    }

    public function calculateCost(ParkingRecord $record, int $totalHours): float
    {
        $vehicleType = $record->getVehicle()->getType();
         
        if(!isset($this->pricingStrategies[$vehicleType])) {
            throw new \DomainException(message: "Estratégia de precificação não encontrada para este tipo de veiculo: " . $vehicleType);
        }

        $strategy = $this->pricingStrategies[$vehicleType];

        return $strategy->getHourlyRate() * $totalHours;
    }
}