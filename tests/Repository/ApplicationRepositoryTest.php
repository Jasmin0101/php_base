<?php

namespace App\Tests\Repository;

use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\StockFixture;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

use function PHPUnit\Framework\assertInstanceOf;

class ApplicationRepositoryTest extends KernelTestCase
{
    protected function setUp():void {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this -> assertInstanceOf(EntityManager::class, $em);

        $loader = new Loader();
        $loader -> addFixture(new UserFixture());

        $loader -> addFixture(new PortfolioFixture());
        
        $loader -> addFixture(new StockFixture());
        
        $executor = new ORMExecutor($em, new ORMPurger());
        $executor->execute($loader ->getFixtures());


    }
    public function testFindAppropriate(): void
    {
       
        $this-> assertTrue(true);

    }


}
