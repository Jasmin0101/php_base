<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\DealLog;
use App\Enums\ActionEnum;
use App\Repository\DealLogRepository;
use App\Entity\Depositary;

#Дельта показывает, сколько бы вы заработали (или потеряли),
# если бы прямо сейчас продали все оставшиеся акции по последней цене сделки.
class DealLogService
{
    public function __construct(
        private readonly DealLogRepository $dealLogRepository
    ) {
    }

    public function registerDealLog(Application $buyApplication, Application $sellApplication): DealLog
    {
        if ($buyApplication->getAction() === ActionEnum::SELL) {
            return $this->registerDealLog($sellApplication, $buyApplication);
        }

        $dealLog = (new DealLog())
            ->setStock($buyApplication->getStock())
            ->setPrice($buyApplication->getPrice())
            ->setBuyPortfolio($buyApplication  -> getPortfolio()) 
            ->setSellPortfolio($sellApplication -> getPortfolio()) 
            ->setQuantity($buyApplication->getQuantity())// min($buyApplication->getPrice(), $sellApplication->getPrice()) для "комплесных" сделок
        ;

        $this->dealLogRepository->saveDealLog($dealLog);

        return $dealLog;
    }

    public function calculateDelta(Depositary $depositary):int{
        
        # Достаем проданные 
        $sellDealLogs = $depositary->getPortfolio()->getSellDealLogs() -> filter(
            function (DealLog $sellDealLog) use ($depositary) {
                return $depositary->getStock()-> getId() === $sellDealLog->getStock()->getId();
            }
        );
        # Достаем купленные 
        $buyDealLogs = $depositary->getPortfolio()->getBuyDealLogs() -> filter(
            function (DealLog $buyDealLog) use ($depositary) {
                return $depositary->getStock()-> getId() === $buyDealLog->getStock()->getId();
            }
        );

        # Достаем последнюю
        $latestDealLog = $this->dealLogRepository->findLatestByStock($depositary->getStock());


        $investSum = 0.0;
        $actualQuantity = 0;

        foreach($sellDealLogs as $sellDealLog){
            $investSum += $sellDealLog->getPrice() * $sellDealLog->getQuantity();
            $actualQuantity -= $sellDealLog->getQuantity();
        }

        foreach($buyDealLogs as $buyDealLog){
            $investSum -= $buyDealLog->getPrice() * $buyDealLog->getQuantity();
            $actualQuantity += $buyDealLog->getQuantity();
        }


        $actualSum = $actualQuantity *($latestDealLog->getPrice()??0.0);

        return $actualSum - $investSum; // delta

    
    }


}
