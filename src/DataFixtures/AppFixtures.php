<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Tests\TestUtils\Fixtures\UserFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        /**
         * User.
         */
        $users = [UserFixtures::USER, UserFixtures::USER_ADMIN, UserFixtures::USER_SUPER_ADMIN];
        foreach ($users as $k => $v) {
            $entity = new User();
            $entity->setEmail($v['email']);
            $entity->setRoles($v['roles']);
            $entity->setPassword($this->passwordEncoder->hashPassword($entity, $v['password']));
            $entity->setIsVerified(true);
            $entity->setFullName('test user '.$k);
            $entity->setPhone('34234234345');
            $manager->persist($entity);
        }
        $manager->flush();

        /*
         * Category
         */
        for ($i = 0; $i < 5; ++$i) {
            $entity = new Category();
            $entity->setTitle('Category test '.$i);
            $manager->persist($entity);
        }
        $manager->flush();

        /*
         * Product
         */
        for ($i = 0; $i < 5; ++$i) {
            $entity = new Product();
            $entity->setTitle('Product test '.$i);
            $entity->setPrice(random_int(0, 100) + (random_int(0, 10) / 10));
            $entity->setQuantity(random_int(1, 100));
            $entity->setCategory($manager->getRepository(Category::class)->findOneBy(['id' => random_int(1, 5)]));
            $entity->setIsPublished(1);
            $manager->persist($entity);
        }
        $manager->flush();

        /*
         * Order
         */
        for ($i = 0; $i < 5; ++$i) {
            $entity = new Order();
            $entity->setOwner($manager->getRepository(User::class)->findOneBy(['id' => random_int(1, 3)]));
            $entity->setStatus(0);
            $entity->setTotalPrice(random_int(100, 1000));
            $manager->persist($entity);
        }
        $manager->flush();

        /*
         * OrderProduct
         */
        for ($i = 0; $i < 5; ++$i) {
            $entity = new OrderProduct();
            $entity->setAppOrder($manager->getRepository(Order::class)->findOneBy(['id' => random_int(1, 5)]));
            $entity->setProduct($manager->getRepository(Product::class)->findOneBy(['id' => random_int(1, 5)]));
            $entity->setQuantity(random_int(1, 5));
            $entity->setPricePerOne(random_int(100, 5000));
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
