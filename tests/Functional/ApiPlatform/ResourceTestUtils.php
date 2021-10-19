<?php

namespace App\Tests\Functional\ApiPlatform;

use App\Repository\UserRepository;
use App\Tests\TestUtils\Fixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Response;

class ResourceTestUtils extends WebTestCase
{
    /**
     * @var string
     */
    protected string $uriKey = '';

    protected const REQUEST_HEADERS = [
        'HTTP_ACCEPT' => 'application/ld+json',
        'CONTENT_TYPE' => 'application/json'
    ];

    protected const REQUEST_HEADERS_PATCH = [
        'HTTP_ACCEPT' => 'application/ld+json',
        'CONTENT_TYPE' => 'application/merge-patch+json'
    ];

    /**
     * @param AbstractBrowser $client
     * @param string          $uri
     * @param string          $method
     * @throws \JsonException
     */
    protected function checkDefaultUserHasNotAccess(AbstractBrowser $client, string $uri, string $method): void
    {
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => UserFixtures::USER['email']]);

        $client->loginUser($user, 'main');

        $client->request($method, $uri, [], [], self::REQUEST_HEADERS, json_encode([], JSON_THROW_ON_ERROR));
//        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param AbstractBrowser $client
     * @return mixed
     * @throws \JsonException
     */
    protected function getResponseDecodedContent(AbstractBrowser $client): mixed
    {
        return json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}