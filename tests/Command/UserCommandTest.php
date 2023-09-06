<?php

namespace App\Tests\Command;

use App\Entity\City;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class UserCommandTest extends KernelTestCase
{
    public function test_create_user_should_be_create_user_in_console(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('app:user:create');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $city = new City('Minsk', 1);
        $entityManager->persist($city);
        $entityManager->flush();

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['Mixail', 'Sokolov', 'Minsk']);
        $commandTester->execute(['command' => $command->getName()]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Пользователь создан!', $output);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->getUserByName('Mixail');
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('Mixail', $user->getName());
        $this->assertSame('Sokolov', $user->getSurName());
        $this->assertInstanceOf(City::class, $user->getCity());
        $this->assertSame('Minsk', $user->getCity()->getName());
    }

    public function test_edit_user_should_be_edit_user_in_console(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('app:user:edit');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $city = new City('Minsk', 1);
        $entityManager->persist($city);
        $user = new User('Mixail', 'Sokolov', $city);
        $entityManager->persist($user);
        $city = new City('Grodno', 10);
        $entityManager->persist($city);
        $entityManager->flush();

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['Mixail', '', '', '']);
        $commandTester->execute(['command' => $command->getName()]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Пользователь изменён!', $output);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->getUserByName('Mixail');
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('Mixail', $user->getName());
        $this->assertSame('Sokolov', $user->getSurName());
        $this->assertInstanceOf(City::class, $user->getCity());
        $this->assertSame('Minsk', $user->getCity()->getName());

        $commandTester->setInputs(['Mixail', 'Nikolay', 'Ivanovich', 'Grodno']);
        $commandTester->execute(['command' => $command->getName()]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Пользователь изменён!', $output);

        $user = $userRepository->getUserByName('Nikolay');
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('Nikolay', $user->getName());
        $this->assertSame('Ivanovich', $user->getSurName());
        $this->assertInstanceOf(City::class, $user->getCity());
        $this->assertSame('Grodno', $user->getCity()->getName());
    }

    public function test_delete_user_should_be_delete_user_in_console(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('app:user:delete');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $city = new City('Minsk', 1);
        $entityManager->persist($city);
        $user = new User('Mixail', 'Sokolov', $city);
        $entityManager->persist($user);
        $entityManager->flush();

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['Mixail']);
        $commandTester->execute(['command' => $command->getName()]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Пользователь успешно удалён!', $output);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->getUserByName('Mixail');
        $this->assertNull($user);
    }
}
