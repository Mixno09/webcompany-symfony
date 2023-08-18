<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct()
    {
    }

    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setName($this->faker->firstName);
            $user->setSurName($this->faker->lastName);
            $user->setCity($this->getReference(CityFixtures::CITY_REFERENCE . '_' . $this->faker->numberBetween(0, 9)));
            $user->setAvatar(new FileValueObject($this->faker->firstName . '.' . $this->faker->fileExtension, $this->faker->mimeType, $this->faker->randomDigit));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
