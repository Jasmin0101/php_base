<?php

namespace App\Tests\Fixture;

use App\Entity\DealLog;
use App\Entity\Portfolio;
use App\Entity\Stock;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DealLogFixture extends AbstractFixture implements DependentFixtureInterface
{
    public const OLDER_DEAL_LOG = 'older-deal-log';
    public const NEWER_DEAL_LOG = 'newer-deal-log';

    public function load(ObjectManager $manager): void
    {
        $olderDealLog = new DealLog();
        $olderDealLog
            ->setPrice(1)
            ->setQuantity(1)
            ->setTimestamp(new \DateTimeImmutable('2025-01-01 00:00:00'))
            ->setBuyPortfolio(
                $this->getReference(PortfolioFixture::PORTFOLIO_USER_REFERENCE, Portfolio::class)
            )
            ->setSellPortfolio(
                $this->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class)
            )
            ->setStock(
                $this->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class)
            )
        ;

        $manager->persist($olderDealLog);

        $this->addReference(self::OLDER_DEAL_LOG, $olderDealLog);

        $newerDealLog = new DealLog();
        $newerDealLog
            ->setPrice(2)
            ->setQuantity(2)
            ->setTimestamp(new \DateTimeImmutable('2025-01-02 00:00:00'))
            ->setBuyPortfolio(
                $this->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class)
            )
            ->setSellPortfolio(
                $this->getReference(PortfolioFixture::PORTFOLIO_USER_REFERENCE, Portfolio::class)
            )
            ->setStock(
                $this->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class)
            )
        ;

        $manager->persist($newerDealLog);
        $manager->flush();

        $this->addReference(self::NEWER_DEAL_LOG, $newerDealLog);
    }

    public function getDependencies(): array
    {
        return [StockFixture::class, PortfolioFixture::class];
    }
}