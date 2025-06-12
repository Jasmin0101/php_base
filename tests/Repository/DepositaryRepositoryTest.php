<?php

namespace App\Tests\Repository;
use App\Entity\Depositary;
use App\Repository\DepositaryRepository;
use App\Tests\Fixture\DepositaryFixture;
use App\Tests\Fixture\StockFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DepositaryRepositoryTest extends KernelTestCase
{
    private StockFixture $stockFixture;
    private DepositaryFixture $depositaryFixture;
    private DepositaryRepository $depositaryRepository;
    private ORMExecutor $executor;


    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManager::class, $em);

        $loader = new Loader();
        $loader-> addFixture($this->stockFixture = new StockFixture());
        $loader->addFixture($this->depositaryFixture = new DepositaryFixture());

        $this->executor = new ORMExecutor($em, new ORMPurger());
        $this->executor->execute($loader->getFixtures());
        $this->depositaryRepository = $em->getRepository(Depositary::class);
    }

       protected function tearDown(): void
    {
        $this -> executor -> getPurger()->purge();
    }

   public function testRemoveDepositary(): void
{
    $depositary = $this->depositaryFixture->getReference(DepositaryFixture::DEPOSITARY_REFERENCE);
    $this->assertInstanceOf(Depositary::class, $depositary);

    $id = $depositary->getId(); 

    $this->depositaryRepository->removeDepositary($depositary);

    $removedDepositary = $this->depositaryRepository->find($id); // ⬅️ Используем сохранённый ID
    $this->assertNull($removedDepositary, 'The depositary should be removed from the database.');
    
}

}
