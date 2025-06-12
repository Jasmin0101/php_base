<?php

namespace App\Tests\Repository;

use App\Entity\Hello;
use App\Repository\HelloRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;


class HelloRepositoryTest extends KernelTestCase
{
    private EntityManager $em;
    private HelloRepository $helloRepository;
    private ORMExecutor $executor;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
 
        $this->assertSame('test', $kernel->getEnvironment());
         $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManager::class, $em);

        $this->executor = new ORMExecutor($em, new ORMPurger());

        $this->helloRepository = $em->getRepository(Hello::class);

    }

    protected function tearDown(): void
    {
        $this->executor->getPurger()->purge();
    }

    public function testCreateLuckyNumber(): void
    {
        $number = '777';

        $hello = $this->helloRepository->createLuckyNumber($number);

        $this->assertInstanceOf(Hello::class, $hello);
        $this->assertNotNull($hello->getId());
        $this->assertEquals($number, $hello->getLuckyNumber());

        $found = $this->helloRepository->find($hello->getId());
        $this->assertNotNull($found);
        $this->assertEquals($number, $found->getLuckyNumber());
    }
}
