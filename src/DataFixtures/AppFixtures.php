<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\File as Avatar;
use App\Entity\User;
use App\Services\FileUploader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AppFixtures extends Fixture
{
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function load(ObjectManager $manager): void
    {
        $names = [
            'Mixail' => 'Ivanov',
            'Nikolay' => 'Turchak',
            'Eugeniy' => 'Undra',
            'Alexey' => 'Gavrilou',
            'Dmitriy' => 'Sauk',
            'Maxim' => 'Petrov',
        ];

        foreach ($names as $name => $surName) {
            $user = new User($name, $surName, $this->loadCity($manager));
            $user->setAvatar($this->loadFile($manager));

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function loadCity(ObjectManager $manager): City
    {
        $citiesData = [
            1 => 'Витебск',
            19 => 'Брест',
            8 => 'Могилев',
            3 => 'Минск',
            5 => 'Гомель',
            0 => 'Гродно',
        ];

        $city = new City();

        $idx = array_rand($citiesData);

        $city->setIdx($idx);
        $city->setName($citiesData[$idx]);

        $manager->persist($city);

        return $city;
    }

    private function loadFile(ObjectManager $manager): Avatar
    {
        $paths = [
            __DIR__ . '/avatars/83723_pla_carny_adult_rindpur_400g_1.jpg',
            __DIR__ . '/avatars/gs_5020.jpg',
            __DIR__ . '/avatars/images.jpg',
            __DIR__ . '/avatars/images (1).jpg',
            __DIR__ . '/avatars/placeholder.png',
        ];

        $key = array_rand($paths);

        $file = new File($paths[$key]);

        $fileSystem = new Filesystem();
        $targetPath = sys_get_temp_dir() . '/' . $file->getBasename();
        $fileSystem->copy($paths[$key], $targetPath);
        $file = new File($targetPath);

        $uploadedFile = new UploadedFile($file->getRealPath(), $file->getBasename(), $file->getMimeType(), test: true);

        $avatar = $this->fileUploader->upload($uploadedFile);

        $manager->persist($avatar);

        return $avatar;
    }
}
