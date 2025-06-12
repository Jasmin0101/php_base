<?php

namespace App\Tests\Fixture;

use App\Entity\Depositary;
use App\Entity\Portfolio;
use App\Entity\Stock;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DepositaryFixture extends ApplicationFixture
{
    public const DEPOSITARY_REFERENCE = 'test_depositary';

    public function load(ObjectManager $manager): void
    {
        $depositary = new Depositary();
        $depositary->setStock(
            $this ->   getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class)
        );
        $depositary->setPortfolio(
            $this->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class)
        );

        $depositary->setQuantity(100);
        $depositary->setFreezeQuantity(0);

        $manager->persist($depositary);
        $manager->flush();

        $this->addReference(self::DEPOSITARY_REFERENCE, $depositary);
    }
}