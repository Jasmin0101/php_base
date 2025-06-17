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


class ApplicationControllerTest extends WebTestCase
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
        parent::tearDown();
        $this->executor->getPurger()->purge();
    }

    public function testIndex(): void
    {       
        $this->client->request('GET', '/application');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table'); 
    }

    public function testGlass(): void
    {  
        /** @var Stock $stock */
        $stock = $this->executor->getReferenceRepository()->getReference(StockFixture::STOCK_TEST_REFERENCE);

        $this->client->request('GET', '/application/glass/' . $stock->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $stock->getName()); 
    }

//    public function testNew(): void
// {
//     $crawler = $this->client->request('GET', '/application/new');

//     $this->assertResponseIsSuccessful();
//     $this->assertSelectorExists('form'); 
//     $application = $this ->executor->getReferenceRepository()->getReference(ApplicationFixture::ADMIN_APPLICATION_REFERENCE);
//     $formData = [
//         'application' => [
//             'price' => 123.45,
//             'quantity' => 1 ,
//             'action' => $application -> getAction() -> value, 
//             'portfolio' =>$application -> getPortfolio() ->getId(),
//             'stock' => $application -> getStock() -> getId(),
//         ],
//     ];

    
//     $this->client->submitForm('Save', $formData); 

//     $this->assertResponseRedirects('/application');

//     $this->client->followRedirect();
//     $this->assertResponseIsSuccessful();

//     /** @var Application|null $application */
//     $application = $this->em->getRepository(Application::class)->findOneBy([
//         'price' => 123.45,
//         'quantity' => 1,
//     ]);
//     $this->assertNotNull($application, 'Application was saved in the database');
// }


}
