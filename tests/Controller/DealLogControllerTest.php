<?php

namespace App\Tests\Controller;
use App\DataFixtures\AppFixtures;
use App\Entity\Application;
use App\Entity\Stock;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixture\ApplicationFixture;
use App\Tests\Fixture\StockFixture;
use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DealLogControllerTest extends WebTestCase
{
  
    private KernelBrowser $client;
    private ORMExecutor $executor;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $loader = new Loader();
        $loader->addFixture(new AppFixtures());
        $loader ->addFixture(new ApplicationFixture());
        $loader->addFixture(new UserFixture());
        $loader->addFixture(new StockFixture());
        $loader->addFixture(new PortfolioFixture());

        $this->executor = new ORMExecutor($this->em, new ORMPurger());
        $this->executor->execute($loader->getFixtures());

      
        /** @var UserRepository $userRepository */
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $userAdmin */
        $userAdmin = $userRepository->findOneBy(['username' => 'admin']);

        $this->client->loginUser($userAdmin);
    }

    protected function tearDown(): void
    {
        $this->executor->getPurger()->purge();
    }
    
     public function testDealLog(): void
    {
        /** @var Stock $stock */
        $stock = $this->executor->getReferenceRepository()->getReference(StockFixture::STOCK_TEST_REFERENCE);

        $this->client->request('GET', '/deal/log/' . $stock->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('', $stock->getName());
    }

}
