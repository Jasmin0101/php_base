<?php

namespace App\Tests\Fixture;

use App\Entity\Application;
use App\Entity\Portfolio;
use App\Entity\Stock;
use App\Enums\ActionEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ApplicationFixture extends AbstractFixture implements DependentFixtureInterface
{
    public const ADMIN_APPLICATION_REFERENCE = 'application-admin';
    public function load(ObjectManager $manager): void
    {
        $application = new Application();
        $application->setPrice(1);
        $application->setQuantity(1);
        $application->setAction(ActionEnum::SELL);
        $application->setPortfolio(
            $this->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class)
        );
        $application->setStock(
            $this->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class)
        );

        $manager->persist($application);
        $manager->flush();

        $this->addReference(self::ADMIN_APPLICATION_REFERENCE, $application);
    }

    public function getDependencies(): array
    {
        return [PortfolioFixture::class, StockFixture::class];
    }
}