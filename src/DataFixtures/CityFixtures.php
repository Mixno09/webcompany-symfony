<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture
{
    public const CITY_REFERENCE = 'city';

    public function __construct()
    {
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $city = new City();
            $city->setName($this->faker->city);
            $city->setIdx($this->faker->randomDigitNotNull);
            $this->addReference(self::CITY_REFERENCE . '_' . $i, $city);
            $manager->persist($city);
        }

        $manager->flush();
    }
}
