<?php

namespace App\Tests\Functional\ApiPlatform;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Tests\TestUtils\Fixtures\UserFixtures;

/**
 * @group functional
 */
class ProductResourceTest extends ResourceTestUtils
{
    /**
     * @var string
     */
    protected string $uriKey = '/api/products';

    public function testGetProducts(): void
    {
        $client = self::createClient();

        $client->request('GET', $this->uriKey, [], [], self::REQUEST_HEADERS);

        self::assertResponseStatusCodeSame(200);
    }

    public function testGetProduct(): void
    {
        $client = self::createClient();

        /** @var Product $product */
        $product = static::getContainer()->get(ProductRepository::class)->findOneBy([]);

        $uri = $this->uriKey.'/'.$product->getUuid();

        $client->request('GET', $uri, [], [], self::REQUEST_HEADERS);

        self::assertResponseStatusCodeSame(200);
    }

    /**
     * @throws \JsonException
     */
    public function testCreateProduct(): void
    {
        $client = self::createClient();

        $this->checkDefaultUserHasNotAccess($client, $this->uriKey, 'POST');

        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => UserFixtures::USER_ADMIN['email']]);

        $client->loginUser($user, 'main');

        $context = [
            'title' => 'New Product',
            'price' => '100',
            'quantity' => 5
        ];

        $client->request('POST', $this->uriKey, [], [], self::REQUEST_HEADERS, json_encode($context, JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);
    }

    /**
     * @throws \JsonException
     */
    public function testPatchProduct(): void
    {
        $client = self::createClient();

        /** @var Product $product */
        $product = static::getContainer()->get(ProductRepository::class)->findOneBy([]);

        $uri = $this->uriKey.'/'.$product->getUuid();

        $this->checkDefaultUserHasNotAccess($client, $uri, 'PATCH');

        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => UserFixtures::USER_ADMIN['email']]);

        $client->loginUser($user, 'main');

        $context = [
            'title' => 'Updated Product',
        ];

        $client->request('PATCH', $uri, [], [], self::REQUEST_HEADERS_PATCH, json_encode($context, JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(200);
    }
}