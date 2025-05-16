<?php

namespace App\Tests\Repository;

use App\Entity\DealLog;
use App\Entity\Stock;
use App\Repository\DealLogRepository;
use App\Tests\Fixture\DealLogFixture;
use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\StockFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DealLogRepositoryTest extends KernelTestCase
{
    private StockFixture $stockFixture;
    private PortfolioFixture $portfolioFixture;
    private DealLogFixture $dealLogFixture;

    private DealLogRepository $dealLogRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManager::class, $em);

        $loader = new Loader();
        $loader->addFixture($this->stockFixture = new StockFixture());
        $loader->addFixture($this->portfolioFixture = new PortfolioFixture());
        $loader->addFixture($this->dealLogFixture = new DealLogFixture());

        (new ORMExecutor($em, new ORMPurger()))->execute($loader->getFixtures());

        $this->dealLogRepository = $em->getRepository(DealLog::class);
    }

    public function testFindByStock(): void
    {
        $findableStock = $this->stockFixture->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class);
        $dealLogs = $this->dealLogRepository->findByStock($findableStock);

        $this->assertCount(2, $dealLogs);
        foreach ($dealLogs as $dealLog) {
            $this->assertEquals($findableStock, $dealLog->getStock());
        }
    }

    public function testNotFoundByStock(): void
    {
        $dealLogs = $this->dealLogRepository->findByStock(
            $this->stockFixture->getReference(StockFixture::STOCK_ANOTHER_REFERENCE, Stock::class),
        );

        $this->assertEmpty($dealLogs);
    }

    public function testFindLatestByStock(): void
    {
        $findableStock = $this->stockFixture->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class);

        $latestDealLog = $this->dealLogRepository->findLatestByStock($findableStock);
        $this->assertEquals(
            $this->dealLogFixture->getReference(DealLogFixture::NEWER_DEAL_LOG, DealLog::class),
            $latestDealLog
        );
    }

    public function testFindLatestByStockNotFound(): void
    {
        $this->assertNull(
            $this->dealLogRepository->findLatestByStock(
                $this->stockFixture->getReference(StockFixture::STOCK_ANOTHER_REFERENCE, Stock::class),
            )
        );
    }
}