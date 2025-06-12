<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // Можно добавить загрузку фикстур и логин, если нужно для защищённых страниц
    }

    public function testLoginPageLoadsSuccessfully(): void
    {
        $crawler = $this->client->request('GET', '/login');

         $this->assertResponseStatusCodeSame(200);
    
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

public function testLoginWithInvalidCredentialsShowsError(): void
{
    $crawler = $this->client->request('GET', '/login');
    $form = $crawler->selectButton('Sign in')->form([
        '_username' => 'wronguser',
        '_password' => 'wrongpassword',
    ]);

    $this->client->submit($form);

    // Проверяем, что произошёл редирект (302) обратно на /login
    $this->assertResponseStatusCodeSame(302);
    $this->assertResponseRedirects('/login');

    // Переходим по редиректу, чтобы получить страницу с ошибкой
    $crawler = $this->client->followRedirect();

    $this->assertResponseIsSuccessful();

    // Проверяем, что на странице есть сообщение об ошибке (корректно укажите селектор)
    $this->assertSelectorTextContains('.alert-danger, .error', 'Invalid credentials');
}


    public function testLogoutRedirects(): void
    {
        $this->client->request('GET', '/logout');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect() ||
            $this->client->getResponse()->isRedirection(),
            'Logout should redirect.'
        );

         $this->assertStringContainsString('/login', $this->client->getResponse()->headers->get('Location'));
    }
}
