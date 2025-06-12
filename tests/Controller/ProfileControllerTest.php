<?php


namespace App\Tests\Controller;

use App\Entity\Portfolio;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    private PortfolioFixture $portfolioFixture;
    private KernelBrowser $client;
    private ORMExecutor $executor;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $loader = new Loader();
        $loader->addFixture(new UserFixture());
        $loader->addFixture($this->portfolioFixture = new PortfolioFixture());

        $this->executor = new ORMExecutor($em, new ORMPurger());
        $this->executor->execute($loader->getFixtures());
    }

    protected function tearDown(): void
    {
        $this->executor->getPurger()->purge();
    }

    public function testProfile(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $userAdmin */
        $userAdmin = $userRepository->findOneBy(['username' => 'admin']);

        $this->client->loginUser($userAdmin);

        $crawler = $this->client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $crawler->filter('h1'));
        $this->assertPageTitleSame('User Profile');
        $this->assertAnySelectorTextSame('h1', "User name: {$userAdmin->getUsername()}");
        $this->assertAnySelectorTextSame('h1', "All portfolios:");

        $adminPortfolio = $this->portfolioFixture->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class);
        $this->assertSelectorTextSame('span', "Portfolio {$adminPortfolio->getId()} has {$adminPortfolio->getBalance()} money and has stocks:");
        $this->assertAnySelectorTextContains('h1', "quantity");
    }
}
