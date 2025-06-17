<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ExternalApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }


    public function testDepositEndpointIsSuccessful(): void
    {
        $this->client->request('GET', '/external/deposit');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Ставка', $this->client->getResponse()->getContent());
    }

 
    public function testDepositFetchesFromApiWhenCacheIsEmpty(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse->method('toArray')->willReturn([
            'RawData' => [
                [
                    'dt' => '2025-06',
                    'obs_val' => 6.5,
                    'element_id' => 1
                ]
            ],
            'headerData' => [
                [
                    'id' => 1,
                    'elname' => 'API значение'
                ]
            ]
        ]);

        $mockHttpClient->method('request')->willReturn($mockResponse);

        self::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $this->client->request('GET', '/external/deposit');
        
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Ставка', $this->client->getResponse()->getContent());
    }
}
