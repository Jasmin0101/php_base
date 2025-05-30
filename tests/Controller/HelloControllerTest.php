<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HelloControllerTest extends WebTestCase
{
    public function testHello(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/hello');
        $this->assertResponseIsSuccessful();
        $this -> assertSelectorTextContains('p', 'Hello World!');

    }

   /**
    * @dataProvider provideHelloNames
    */
    public function testHelloName(string $name): void{

        $client = static::createClient();
        $crawler = $client->request('GET', "/hello/$name");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('p',"Hello $name");
        $this -> assertEquals("Hello $name", $client -> getResponse()-> getContent() );

    }

  
    public static function provideHelloNames(): array
    {
        return [
            'First name' => ['Ivan'],
            'Second name' => ['Petr'],
            'Third name' => ['Mina'],
            'Forth name' => ['Lirili Larila'],
        ];
    }
}
