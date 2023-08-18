<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\User;
use App\Services\FileUploader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
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
        $cities = $this->loadCity($manager);
        $avatars = $this->loadFile($manager);

        $users = [];
        foreach ($names as $name => $surName) {
            $city = next($cities); //todo плохо
            if ($city !== false) {
                $user = new User($name, $surName, $city);
            }

            $avatar = next($avatars);
            if ($avatar !== false) {
                $user->setAvatar($avatar);
            }

            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @return City[]
     */
    private function loadCity(ObjectManager $manager): array
    {
        $citiesData = [
            1 => 'Витебск',
            19 => 'Брест',
            8 => 'Могилев',
            3 => 'Минск',
            5 => 'Гомель',
            0 => 'Гродно',
        ];

        $cities = [];
        foreach ($citiesData as $index => $cityName) {
            $city = new City();

            $city->setName($cityName);
            $city->setIdx($index);

            $manager->persist($city);

            $cities[] = $city;
        }

        return $cities;
    }

    private function loadFile(ObjectManager $manager): array
    {
        $paths = [
            __DIR__ . '/avatars/83723_pla_carny_adult_rindpur_400g_1.jpg',
            __DIR__ . '/avatars/gs_5020.jpg',
            __DIR__ . '/avatars/images.jpg',
            __DIR__ . '/avatars/images (1).jpg',
            __DIR__ . '/avatars/placeholder.png',
        ];

        $files = [];
        foreach ($paths as $path) {
            $file = new File($path);

            $uploaderFile = new UploadedFile($file->getRealPath(), $file->getBasename(), $file->getMimeType(), test: true);
            $avatar = $this->fileUploader->upload($uploaderFile); //todo files move and delete

            $manager->persist($avatar);

            $files[] = $avatar;
        }

        return $files;
    }
}
