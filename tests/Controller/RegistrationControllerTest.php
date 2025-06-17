<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use App\Tests\Fixture\ApplicationFixture;
use App\Tests\Fixture\StockFixture;
use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    private $client;
    private ORMExecutor $executor;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $loader = new Loader();
        $loader->addFixture(new AppFixtures());
        $loader->addFixture(new ApplicationFixture());
        $loader->addFixture(new UserFixture());
        $loader->addFixture(new StockFixture());
        $loader->addFixture(new PortfolioFixture());

        $this->executor = new ORMExecutor($this->em, new ORMPurger());
        $this->executor->execute($loader->getFixtures());

        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $userAdmin = $userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($userAdmin);
    }

    protected function tearDown(): void
    {
        $this->executor->getPurger()->purge();
        parent::tearDown();
    }

    public function testRegistrationPageLoadsSuccessfully(): void
    {
        $crawler = $this->client->request('GET', '/register');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name=registration_form]');
        $this->assertSelectorTextContains('button', 'Register');
    }

    public function testSuccessfulRegistration(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Register')->form();

        $form['registration_form[username]'] = 'newuser@example.com';
        $form['registration_form[plainPassword]'] = 'StrongPass123!';
        $form['registration_form[agreeTerms]'] = true ;

        $this->client->submit($form);

        $this->assertResponseRedirects('/profile');

        $this->client->followRedirect();

        $this->assertSelectorExists('h1');
        $this->assertSelectorTextContains('h1', 'User name');
    }

   public function testRegistrationWithInvalidDataShowsErrors(): void
{
    $crawler = $this->client->request('GET', '/register');
    $form = $crawler->selectButton('Register')->form();

    $form['registration_form[username]'] = ''; 
    $form['registration_form[plainPassword]'] = ''; 
      $crawler = $this->client->submit($form);

    $this->assertResponseStatusCodeSame(422);
    $this->assertSelectorExists('.invalid-feedback');
}
}
