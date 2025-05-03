<?php

namespace App\Tests\Fixture;

use App\Entity\Stock;
use App\Entity\User;
use App\Enums\ActionEnum;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StockFixture extends AbstractFixture 
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $testStock = new Stock();
        $testStock  -> setName('TestFixture');
        $testStock -> setTicker('TSF');
        
        $manager -> persist($testStock);
        $manager -> flush();


        $this->addReference('stock-test', $testStock);        
    }

   
}
