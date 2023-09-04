<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

        $city = new City('Minsk', 1);
        $entityManager->persist($city);
        $entityManager->flush();

        $crawler = $client->request('GET', '/create/user');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="user"]');
        $this->assertFormValue('form[name="user"]', 'user[city]', (string) $city->getId());

        $form = $crawler->filter('form[name="user"]')->form();

        /** @var FileUploader $fileUploader */
        $fileUploader = self::getContainer()->get(FileUploader::class);

        $client->submit($form, [
            'user[name]' => 'Mixail',
            'user[surName]' => 'Sokolov',
            'user[city]' => $city->getId(),
            'user[file]' => __DIR__ . '/../files/avatar-1.jpg',
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

        $city = new City('Minsk', 1);
        $entityManager->persist($city);

        $newCity = new City('Brest', 10);
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

        $city = new City('Minsk', 1);
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

    /**
     * @dataProvider getSortFieldAndSortDirection
     */
    public function test_users_should_be_sort_user(array $users, array $formParams, array $formValues, array $expectedResult): void
    {
        $client = self::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        /** @var \App\Entity\User[] $users */
        foreach ($users as $user) {
            $entityManager->persist($user->getCity());
            $entityManager->persist($user);
        }
        $entityManager->flush();

        if (array_key_exists('cityName', $formParams)) {
            $cityRepository = self::getContainer()->get(CityRepository::class);
            $minsk = $cityRepository->findOneBy(['name' => $formParams['cityName']]);
            $cityId = $minsk->getId();
            $formValues['cityId'] = $cityId;
        }

        $crawler = $client->request('GET', '/user', ['form' => '1']);
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[id="formSort"]')->form();

        $crawler = $client->submit($form, $formValues);

        $this->assertResponseIsSuccessful();
        $actualResult = $crawler->filter('.userdan h4')->extract(['_text']);

        $this->assertSame($expectedResult, $actualResult);
    }

    public function getSortFieldAndSortDirection(): Generator
    {
        $users = [];

        $city = new City('Brest', 1);

        $user = new User('Andrey', 'Andreev', $city);
        $users[] = $user;

        $city = new City('Grodno', 2);

        $user = new User('Boris', 'Borisov', $city);
        $users[] = $user;

        $city = new City('Minsk', 3);

        $user = new User('Dima', 'Dmitriev', $city);
        $users[] = $user;

        $user = new User('Igor', 'Igorev', $city);
        $users[] = $user;

        $formParams = [
            'cityName' => 'Minsk',
        ];
        $formValues = [
            'orderBy' => 'name',
            'order' => 'ASC',
        ];
        $expectedResult= ['Dima Dmitriev', 'Igor Igorev'];
        yield [$users, $formParams, $formValues, $expectedResult];

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $formParams = [
            'cityName' => 'Minsk',
        ];
        $formValues = [
            'orderBy' => 'name',
            'order' => 'DESC',
        ];
        $expectedResult= ['Igor Igorev', 'Dima Dmitriev'];
        yield [$users, $formParams, $formValues, $expectedResult];

        $formValues = [
            'orderBy' => 'name',
            'order' => 'ASC',
        ];
        $expectedResult= ['Andrey Andreev', 'Boris Borisov', 'Dima Dmitriev', 'Igor Igorev'];
        yield [$users, [], $formValues, $expectedResult];

        $formValues = [
            'orderBy' => 'name',
            'order' => 'DESC',
        ];
        $expectedResult = ['Igor Igorev', 'Dima Dmitriev', 'Boris Borisov', 'Andrey Andreev'];
        yield [$users, [], $formValues, $expectedResult];

        $formValues = [
            'orderBy' => 'surname',
            'order' => 'ASC',
        ];
        $expectedResult = ['Andrey Andreev', 'Boris Borisov', 'Dima Dmitriev', 'Igor Igorev'];
        yield [$users, [], $formValues, $expectedResult];

        $formValues = [
            'orderBy' => 'surname',
            'order' => 'DESC',
        ];
        $expectedResult = ['Igor Igorev', 'Dima Dmitriev', 'Boris Borisov', 'Andrey Andreev'];
        yield [$users, [], $formValues, $expectedResult];
    }
}
