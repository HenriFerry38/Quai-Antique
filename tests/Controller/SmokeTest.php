<?php
namespace App\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
class SmokeTest extends WebTestCase
{
    public function testApiDocUrlIsSuccessful(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('GET', '/api/doc');
        self::assertResponseIsSuccessful();
    }

    public function testApiAccountUrlIsSecure(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('GET', '/api/me');
        self::assertResponseStatusCodeSame(401);
    }
    
    public function testLoginRouteCanConnectAValidUser(): void 
    {
        $client = self::createClient();
        $client->followRedirects(false);

        $client->request('POST','/api/registration', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'firstName' => 'testo',
            'lastName' => 'test',
            'email' => 'test@test.fr',
            'password' => 'test'
        ], JSON_THROW_ON_ERROR));

        $client->request('POST','/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'test@test.fr',
            'password' => 'test',
        ], JSON_THROW_ON_ERROR));

        $statusCode = $client->getResponse()->getStatusCode();
        dd($statusCode);
    }
}