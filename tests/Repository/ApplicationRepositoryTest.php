<?php

namespace App\Tests\Repository;

use App\Entity\Application;
use App\Entity\Portfolio;
use App\Entity\Stock;
use App\Entity\User;
use App\Enums\ActionEnum;
use App\Repository\ApplicationRepository;
use App\Tests\Fixture\ApplicationFixture;
use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\StockFixture;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApplicationRepositoryTest extends KernelTestCase
{
    private UserFixture $userFixture;
    private StockFixture $stockFixture;
    private PortfolioFixture $portfolioFixture;
    private ApplicationFixture $applicationFixture;

    private ORMExecutor $executor;
    private ApplicationRepository $applicationRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManager::class, $em);

        $loader = new Loader();
        $loader->addFixture($this->stockFixture = new StockFixture());
        $loader->addFixture($this->userFixture = new UserFixture());
        $loader->addFixture($this->portfolioFixture = new PortfolioFixture());
        $loader->addFixture($this->applicationFixture = new ApplicationFixture());

        $this->executor = new ORMExecutor($em, new ORMPurger());
        $this->executor->execute($loader->getFixtures());

        $this->applicationRepository = $em->getRepository(Application::class);
    }

   protected function tearDown(): void
    {
        $this -> executor -> getPurger()->purge();
    }

    public function testFindAppropriate(): void
    {
        $application = $this->getAppropriateApplication();

        $currentApplication = $this->applicationRepository->findAppropriate($application);

        $this->assertEquals(
            $this->applicationFixture->getReference(ApplicationFixture::ADMIN_APPLICATION_REFERENCE, Application::class),
            $currentApplication
        );
    }

    public function testFindAppropriateDifferentPrice(): void
    {
        $application = $this->getAppropriateApplication();
        $application->setPrice(2);

        $nonAppropriateApplication = $this->applicationRepository->findAppropriate($application);
        $this->assertNull($nonAppropriateApplication);
    }

    public function testFindAppropriateDifferentQuantity(): void
    {
        $application = $this->getAppropriateApplication();
        $application->setQuantity(2);

        $nonAppropriateApplication = $this->applicationRepository->findAppropriate($application);
        $this->assertNull($nonAppropriateApplication);
    }

    public function testFindAppropriateSameAction(): void
    {
        $application = $this->getAppropriateApplication();
        $application->setAction(ActionEnum::SELL);

        $nonAppropriateApplication = $this->applicationRepository->findAppropriate($application);
        $this->assertNull($nonAppropriateApplication);
    }

    public function testFindAppropriateSamePortfolio(): void
    {
        $application = $this->getAppropriateApplication();
        $application->setPortfolio($this->portfolioFixture->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class));

        $nonAppropriateApplication = $this->applicationRepository->findAppropriate($application);
        $this->assertNull($nonAppropriateApplication);
    }

    public function testFindAppropriateDifferentStock(): void
    {
        $application = $this->getAppropriateApplication();
        $application->setStock($this->stockFixture->getReference(StockFixture::STOCK_ANOTHER_REFERENCE, Stock::class));

        $nonAppropriateApplication = $this->applicationRepository->findAppropriate($application);
        $this->assertNull($nonAppropriateApplication);
    }

    public function testFindAllByUser(): void
    {
        $userAdmin = $this->userFixture->getReference(UserFixture::USER_ADMIN_REFERENCE, User::class);
        $applications = $this->applicationRepository->findAllByUser($userAdmin);

        $this->assertCount(1, $applications);
        $this->assertEquals(
            $this->applicationFixture->getReference(ApplicationFixture::ADMIN_APPLICATION_REFERENCE, Application::class),
            $applications[0]
        );
    }

    private function getAppropriateApplication(): Application
    {
        $application = new Application();
        $application->setPrice(1);
        $application->setQuantity(1);
        $application->setAction(ActionEnum::BUY);
        $application->setPortfolio($this->portfolioFixture->getReference(PortfolioFixture::PORTFOLIO_USER_REFERENCE, Portfolio::class));
        $application->setStock($this->stockFixture->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class));

        return $application;
    }
}