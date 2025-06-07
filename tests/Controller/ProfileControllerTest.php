<?php

namespace App\Tests\Controller;

use App\Tests\Fixture\UserFixture;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {

        $this->client = static::createClient();

        $em = self::$kernel->getContainer()->get('doctrine')->getManager();

        $loader = new Loader();
        $loader->addFixture(new UserFixture());

        $executor = new ORMExecutor($em, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    public function testProfile(): void
    {
        $this-> markTestSkipped();
 
        /** @var UserRepository $userRepository */
        $userRepository = $this-> client->getContainer()->get(UserRepository::class);

        $userAdmin = $userRepository->findOneBy(['username' => 'admin']);
        $this->assertNotNull($userAdmin, 'User "admin" not found. Did you load the fixture correctly?');

        $this->client->loginUser($userAdmin);
        $crawler = $this -> client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains("User Profile");
        $this->assertSelectorTextSame('h1', "User Profile");
        // $this->assertSelectorTextSame('h1', "All portfolios:");
        //$this->assertSelectorTextContains('h1', 'Hello World');
        
    }
}
