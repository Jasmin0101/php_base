<?php

namespace App\Repository\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixture\UserFixture;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserFixture $userFixture;
    private UserRepository $userRepository;
    private ORMExecutor $executor;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());

        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManager::class, $em);

        $loader = new Loader();
        $loader->addFixture($this->userFixture = new UserFixture());

        $this->executor = new ORMExecutor($em, new ORMPurger());
        $this->executor->execute($loader->getFixtures());

        $this->userRepository = $em->getRepository(User::class);
    }

    protected function tearDown(): void
    {
        $this->executor->getPurger()->purge();
    }

    public function testUpgradePassword(): void
    {
      
        $user = $this->executor->getReferenceRepository()->getReference(UserFixture::USER_USER_REFERENCE);
        $this->assertInstanceOf(User::class, $user);
        
        $newPassword  = password_hash('new_password', PASSWORD_BCRYPT);


        $this->userRepository->upgradePassword($user, $newPassword);

        $updatedUser = $this->userRepository->find($user->getId());
        $this->assertNotNull($updatedUser);
        $this->assertEquals($newPassword, $updatedUser->getPassword());
    }
}
