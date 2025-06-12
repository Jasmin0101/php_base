<?php

namespace App\Tests\Controller;

use App\Entity\Stock;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\StockFixture;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StockControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ORMExecutor $executor;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $loader = new Loader();
        $loader->addFixture(new UserFixture());
        $loader->addFixture(new StockFixture());

        $this->executor = new ORMExecutor($em, new ORMPurger());
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

    public function testNewStock(): void
    {
        $this->client->request('GET', '/stock/new');

        $crawler = $this->client->submitForm('Save', [
            'stock[name]' => 'Admin stock',
            'stock[ticker]' => 'AST'
        ]);

        $this->assertResponseRedirects('/stock');
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
    }

    public function testIndex(): void
    {    /** @var Stock $stock */
        $stock = $this->executor->getReferenceRepository()->getReference(StockFixture::STOCK_TEST_REFERENCE);

        $this->client->request('GET', '/stock');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $stock->getName());
    }

    public function testShow(): void
    {
        //         С помощью этой аннотации ты подсказываешь:
        // "Убедись, что это объект класса Stock, и дай мне автодополнение, подсветку и проверки типов."


        /** @var Stock $stock */
        $stock = $this->executor->getReferenceRepository()->getReference(StockFixture::STOCK_TEST_REFERENCE);

        $this->client->request('GET', '/stock/' . $stock->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $stock->getName());
    }



    public function testEditWithValidForm():void{
        /** @var Stock $stock */
        $stock = $this->executor->getReferenceRepository()->getReference(StockFixture::STOCK_TEST_REFERENCE);

        $this->client->request('GET', '/stock/' . $stock->getId() . '/edit');
        $this->assertResponseIsSuccessful();


        $this->client->submitForm('Update', [
        'stock[name]' => 'Updated stock',
        'stock[ticker]' => 'UPD',
        ]);

      
        $this->assertResponseRedirects('/stock');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Updated stock');
    }

public function testEditWithoutValidForm(): void
{
    /** @var Stock $stock */
    $stock = $this->executor->getReferenceRepository()->getReference(StockFixture::STOCK_TEST_REFERENCE);

    
    $this->client->request('GET', '/stock/' . $stock->getId() . '/edit');

    $this->client->submitForm('Update', [
        'stock[name]' => '  ',
        'stock[ticker]' => 'UPD',
    ]);
    $this->assertResponseStatusCodeSame(500);
}

//    public function testDeleteStock(): void
// {


//     $this->client->request('GET', '/');

//     /** @var Stock $stock */
//     $stock = $this->executor->getReferenceRepository()->getReference(StockFixture::STOCK_TEST_REFERENCE);

//     $csrfToken = $this->client->getContainer()
//         ->get('test.service_container')
//         ->get('security.csrf.token_manager')
//         ->getToken('delete' . $stock->getId())
//         ->getValue();

//     $this->client->request('POST', '/stock/' . $stock->getId(), [
//         '_token' => $csrfToken,
//     ]);

//     $this->assertResponseRedirects('/stock');

//     $this->client->followRedirect();
//     $this->assertResponseIsSuccessful();

//     /** @var EntityManagerInterface $em */
//     $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

//     $deletedStock = $em->getRepository(Stock::class)->find($stock->getId());
//     $this->assertNull($deletedStock);
// }


}