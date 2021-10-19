<?php

namespace App\Tests\Functional\Controller\Main;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 */
class DefaultControllerTest extends WebTestCase
{
    public function testRedirectEmptyUrlToLocale(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseRedirects(
            'http://localhost/ru',
            Response::HTTP_MOVED_PERMANENTLY,
            sprintf('The %s URL redirects to the version with locale', '/')
        );
    }

    /**
     * @dataProvider getPublicUrls
     */
    public function testPublicUrls(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful(
            sprintf('The %s public URL loads correctly', $url)
        );
    }

    /**
     * @dataProvider getSecureUrls
     */
    public function testSecureUrls(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseRedirects(
            '/en/login',
            Response::HTTP_FOUND,
            sprintf('The %s URL redirects to the login page', '/')
        );
    }

    public function getPublicUrls(): ?\Generator
    {
        yield ['/en/'];
        yield ['/en/login'];
        yield ['/en/register'];
        yield ['/en/reset-password'];
    }

    public function getSecureUrls(): ?\Generator
    {
        yield ['/en/profile'];
        yield ['/en/profile/edit'];
    }
}
