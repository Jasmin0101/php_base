<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\DealLog;
use App\Enums\ActionEnum;
use App\Repository\DealLogRepository;
use App\Entity\Depositary;
use App\Entity\Delta;

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

   public function calculateDelta(Depositary $depositary): Delta
{
    $sellDealLogs = $depositary->getPortfolio()->getSellDealLogs()->filter(
        fn(DealLog $sellDealLog) => $depositary->getStock()->getId() === $sellDealLog->getStock()->getId()
    );

    $buyDealLogs = $depositary->getPortfolio()->getBuyDealLogs()->filter(
        fn(DealLog $buyDealLog) => $depositary->getStock()->getId() === $buyDealLog->getStock()->getId()
    );

    $latestDealLog = $this->dealLogRepository->findLatestByStock($depositary->getStock());

    $investSum = 0.0;
    $actualQuantity = 0;

    foreach ($sellDealLogs as $sellDealLog) {
        $investSum -= $sellDealLog->getPrice() * $sellDealLog->getQuantity();
        $actualQuantity -= $sellDealLog->getQuantity();
    }

    foreach ($buyDealLogs as $buyDealLog) {
        $investSum += $buyDealLog->getPrice() * $buyDealLog->getQuantity();
        $actualQuantity += $buyDealLog->getQuantity();
    }

    $actualSum = $actualQuantity * ($latestDealLog?->getPrice() ?? 0.0);
    $deltaAbsolute = $actualSum - $investSum;

    $deltaPercent = $investSum !== 0.0 ? ($deltaAbsolute / abs($investSum)) * 100 : 0.0;

    return new Delta($deltaAbsolute, $deltaPercent);
}

}
