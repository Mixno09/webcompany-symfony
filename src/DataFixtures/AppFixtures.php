<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\User;
use App\Services\FileUploader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AppFixtures extends Fixture
{
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function load(ObjectManager $manager): void
    {
        $cities = $this->loadCities($manager);
        $avatars = $this->loadAvatars($manager);

        $this->loadUsers($manager, $cities, $avatars);

        $manager->flush();
    }

    /**
     * @return array<string, \App\Entity\City>
     */
    private function loadCities(ObjectManager $manager): array
    {
        $cities = [];

        $city = new City();
        $city->setName('Minsk');
        $city->setIdx(10);
        $manager->persist($city);
        $cities['minsk'] = $city;

        $city = new City();
        $city->setName('Brest');
        $city->setIdx(5);
        $manager->persist($city);
        $cities['brest'] = $city;

        $city = new City();
        $city->setName('Grodno');
        $city->setIdx(0);
        $manager->persist($city);
        $cities['grodno'] = $city;

        return $cities;
    }

    /**
     * @param array<string, \App\Entity\City> $cities
     * @param array<string, \App\Entity\File> $avatars
     */
    private function loadUsers(ObjectManager $manager, array $cities, array $avatars): void
    {
        $user = new User('Mixail', 'Ivanov', $cities['minsk']);
        $user->setAvatar($avatars['avatar-1']);
        $manager->persist($user);

        $user = new User('Nikolay', 'Turchak', $cities['minsk']);
        $user->setAvatar($avatars['avatar-2']);
        $manager->persist($user);

        $user = new User('Eugeniy', 'Undra', $cities['brest']);
        $user->setAvatar($avatars['avatar-3']);
        $manager->persist($user);

        $user = new User('Alexey', 'Gavrilou', null);
        $manager->persist($user);
    }

    /**
     * @return array<string, \App\Entity\File>
     */
    private function loadAvatars(ObjectManager $manager): array
    {
        $avatars = [];

        $uploadedFile = $this->getUploadedFile(__DIR__ . '/avatars/avatar-1.jpg');
        $avatar = $this->fileUploader->upload($uploadedFile);
        $manager->persist($avatar);
        $avatars['avatar-1'] = $avatar;

        $uploadedFile = $this->getUploadedFile(__DIR__ . '/avatars/avatar-2.jpg');
        $avatar = $this->fileUploader->upload($uploadedFile);
        $manager->persist($avatar);
        $avatars['avatar-2'] = $avatar;

        $uploadedFile = $this->getUploadedFile(__DIR__ . '/avatars/avatar-3.jpg');
        $avatar = $this->fileUploader->upload($uploadedFile);
        $manager->persist($avatar);
        $avatars['avatar-3'] = $avatar;

        return $avatars;
    }

    private function getUploadedFile(string $filePath): UploadedFile
    {
        $fileSystem = new Filesystem();

        $file = new File($filePath);
        $targetPath = tempnam(sys_get_temp_dir(), 'avatar');
        $fileSystem->copy($file->getRealPath(), $targetPath, true);

        $file = new File($targetPath);
        return new UploadedFile($file->getRealPath(), $file->getBasename(), $file->getMimeType(), test: true);
    }
}
