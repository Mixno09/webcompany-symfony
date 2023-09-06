<?php

namespace App\Tests\Command;

use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CityCommandTest extends KernelTestCase
{
    public function test_create_city_should_be_create_city_in_console(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $command = $application->find('app:city:create');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['Minsk', 1]);
        $commandTester->execute(['command' => $command->getName()]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Город успешно создан!', $output);

        /** @var CityRepository $cityRepository */
        $cityRepository = self::getContainer()->get(CityRepository::class);
        $city = $cityRepository->getCityByName('Minsk');
        $this->assertInstanceOf(City::class, $city);
    }

    public function test_edit_city_should_be_edit_city_in_console(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:city:edit');
        $commandTester = new CommandTester($command);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $city = new City('Minsk', 1);
        $entityManager->persist($city);
        $entityManager->flush();

        /** @var CityRepository $cityRepository */
        $cityRepository = self::getContainer()->get(CityRepository::class);

        $commandTester->setInputs(['Minsk', 'Minsk', 1]);
        $commandTester->execute(['command' => $command->getName()]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Город отредактирован.', $output);

        $city = $cityRepository->getCityByName('Minsk');
        $this->assertInstanceOf(City::class, $city);
        $this->assertSame('Minsk', $city->getName());
        $this->assertSame(1, $city->getIdx());

        $commandTester->setInputs(['Minsk', 'Vitebsk', 100]);
        $commandTester->execute(['command' => $command->getName()]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Город отредактирован.', $output);

        $city = $cityRepository->getCityByName('Vitebsk');
        $this->assertInstanceOf(City::class, $city);
        $this->assertSame('Vitebsk', $city->getName());
        $this->assertSame(100, $city->getIdx());
    }

    public function test_delete_city_should_be_delete_city_in_console(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:city:delete');
        $commandTester = new CommandTester($command);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $city = new City('Minsk', 1);
        $entityManager->persist($city);
        $entityManager->flush();

        $commandTester->setInputs(['Minsk']);
        $commandTester->execute(['command' => $command->getName()]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Город успешно удалён.', $output);

        /** @var CityRepository $cityRepository */
        $cityRepository = self::getContainer()->get(CityRepository::class);
        $city = $cityRepository->getCityByName('Minsk');
        $this->assertNull($city);
    }
}
