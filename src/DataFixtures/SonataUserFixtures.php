<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\SonataUserUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class SonataUserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new SonataUserUser();

        $user->setUsername('Mixail');
        $user->setEmail('mixno09@mail.ru');
        $user->setPlainPassword('00000');
        $user->setEnabled(true);
        $user->setSuperAdmin(true);

        $manager->persist($user);
        $manager->flush();
    }
}
