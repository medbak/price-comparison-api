<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Infrastructure\Entity\DoctrineApiSource;
use App\Infrastructure\External\MockDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ApiSourceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // API One - ShopA, ShopB, ShopC format
        $apiOneData = MockDataProvider::getApiOneData();

        $apiOne = new DoctrineApiSource(
            'API One - Shopping Sites',
            'https://api.shopping-sites.com/v1/prices',
            $apiOneData,
            'api_one',
            true,
            30
        );

        // API Two - VendorOne, VendorTwo format
        $apiTwoData = MockDataProvider::getApiTwoData();

        $apiTwo = new DoctrineApiSource(
            'API Two - Vendor Network',
            'https://api.vendor-network.com/prices',
            $apiTwoData,
            'api_two',
            true,
            45
        );

        // API Three - Additional source for more variety
        $apiThreeData = MockDataProvider::getApiThreeData();

        $apiThree = new DoctrineApiSource(
            'API Three - Supplier Network',
            'https://api.suppliers.com/pricing',
            $apiThreeData,
            'api_three',
            true,
            60
        );

        $manager->persist($apiOne);
        $manager->persist($apiTwo);
        $manager->persist($apiThree);

        $manager->flush();
    }
}
