<?php

namespace App\Tests\Fixture;

use App\Entity\Application;
use App\Entity\Portfolio;
use App\Entity\Stock;
use App\Enums\ActionEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class ApplicationFixture extends AbstractFixture implements DependentFixtureInterface
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        // TODO: Implement load() method.
        $application = new Application();
        $application->setPrice(1);
        $application->setQuantity(1);
        $application->setAction(ActionEnum::SELL);

        $application -> setStock(  $this -> getReference('stock-test', Portfolio::class));
        
        $application -> setPortfolio(
            $this -> getReference('portfolio-admin', Portfolio::class)
        );
        
    }
    public function getDependencies(): array
    {
        return [PortfolioFixture::class, StockFixture::class];
    }
}
