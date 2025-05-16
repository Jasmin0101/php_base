<?php

namespace App\Tests\Fixture;

use App\Entity\Stock;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class StockFixture extends AbstractFixture
{
    public const STOCK_TEST_REFERENCE = 'stock-test';
    public const STOCK_ANOTHER_REFERENCE = 'stock-another';
    public function load(ObjectManager $manager): void
    {
        $testStock = new Stock();
        $testStock->setName('Test stock');
        $testStock->setTicker('TST');

        $manager->persist($testStock);

        $this->addReference(self::STOCK_TEST_REFERENCE, $testStock);

        $anotherStock = new Stock();
        $anotherStock->setName('Another stock');
        $anotherStock->setTicker('ANS');

        $this->addReference(self::STOCK_ANOTHER_REFERENCE, $anotherStock);
        $manager->persist($anotherStock);

        $manager->flush();
    }
}