<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UserControllerTest extends WebTestCase
{
    public function test_index_should_be_blank_when_no_user(): void
    {
        $client = self::createClient();

        $userRepository = self::getContainer()->get(UserRepository::class);
        $this->assertEquals(0, $userRepository->count([]));

        $client->request('GET', '/user');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(0, '.users');
    }

    public function test_create_should_be_create_user(): void
    {
        $client = self::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $city = new City();
        $city->setName('Minsk');
        $city->setIdx(1);
        $entityManager->persist($city);
        $entityManager->flush();

        $crawler = $client->request('GET', '/create/user');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="user"]');
        $this->assertFormValue('form[name="user"]', 'user[city]', (string) $city->getId());

        $form = $crawler->filter('form[name="user"]')->form();

        /** @var FileUploader $fileUploader */
        $fileUploader = self::getContainer()->get(FileUploader::class);
        $file = $fileUploader->copy(__DIR__ . '/../files/avatar-1.jpg');
        $filePath = $fileUploader->getFilePath($file);

        $client->submit($form, [
            'user[name]' => 'Mixail',
            'user[surName]' => 'Sokolov',
            'user[city]' => $city->getId(),
            'user[file]' => $filePath,
        ]);

        $this->assertResponseRedirects('/user', 302);
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['name' => 'Mixail']);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('Sokolov', $user->getSurName());
        $this->assertSame($city->getId(), $user->getCity()->getId());
        $this->assertFileEquals(
            __DIR__ . '/../files/avatar-1.jpg',
            $fileUploader->getFilePath($user->getAvatar()),
        );
    }

    public function test_edit_should_be_edit_user(): void
    {
        $client = self::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        /** @var FileUploader $fileUploader */
        $fileUploader = self::getContainer()->get(FileUploader::class);

        $city = new City();
        $city->setName('Minsk');
        $city->setIdx(1);
        $entityManager->persist($city);

        $newCity = new City();
        $newCity->setName('Brest');
        $newCity->setIdx(10);
        $entityManager->persist($newCity);

        $user = new User('Mixail', 'Sokolov', $city);
        $avatar = $fileUploader->copy(__DIR__ . '/../files/avatar-1.jpg');
        $entityManager->persist($avatar);
        $user->setAvatar($avatar);
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $client->request('GET', "/edit/{$user->getId()}/user");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="user"]');
        $this->assertFormValue('form[name="user"]', 'user[name]', $user->getName());
        $this->assertFormValue('form[name="user"]', 'user[surName]', $user->getSurName());
        $this->assertFormValue('form[name="user"]', 'user[city]', (string) $user->getCity()->getId());
        $avatarWebPath = $fileUploader->getWebPath($avatar);
        $this->assertSelectorExists("form[name=\"user\"] .image[src=\"{$avatarWebPath}\"]");

        $form = $crawler->filter('form[name="user"]')->form();

        $client->submit($form, [
            'user[name]' => 'Dima',
            'user[surName]' => 'Ivanov',
            'user[city]' => $newCity->getId(),
            'user[file]' => __DIR__ . '/../files/avatar-2.jpg',
        ]);

        $this->assertResponseRedirects("/edit/{$user->getId()}/user", 302);
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->find($user->getId());
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('Dima', $user->getName());
        $this->assertSame('Ivanov', $user->getSurName());
        $this->assertSame($newCity->getId(), $user->getCity()->getId());
        $this->assertFileEquals(
            __DIR__ . '/../files/avatar-2.jpg',
            $fileUploader->getFilePath($user->getAvatar()),
        );
    }

    public function test_delete_should_be_delete_user(): void
    {
        $client = self::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        /** @var FileUploader $fileUploader */
        $fileUploader = self::getContainer()->get(FileUploader::class);

        $city = new City();
        $city->setName('Minsk');
        $city->setIdx(1);
        $entityManager->persist($city);

        $user = new User('Mixail', 'Sokolov', $city);
        $avatar = $fileUploader->copy(__DIR__ . '/../files/avatar-1.jpg');
        $entityManager->persist($avatar);
        $user->setAvatar($avatar);
        $entityManager->persist($user);
        $entityManager->flush();

        $userId = $user->getId();

        $crawler = $client->request('GET', '/user');
        $form = $crawler->filter('.userdan')->children()->filter('form')->form();

        $client->submit($form);
        $this->assertResponseRedirects('/user', 302);

        $userRepository = self::getContainer()->get(UserRepository::class);
        $this->assertNull( $userRepository->find(['id' => $userId]));
    }
}
