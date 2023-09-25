<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\SonataMediaMedia;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AppFixtures extends Fixture
{
    private MediaManagerInterface $mediaManager;

    public function __construct(
        #[Autowire(service: 'sonata.media.manager.media')] MediaManagerInterface $mediaManager,
    ) {
        $this->mediaManager = $mediaManager;
    }

    public function load(ObjectManager $manager): void
    {
        $cities = $this->loadCities($manager);
        $avatars = $this->loadAvatars();

        $this->loadUsers($manager, $cities, $avatars);

        $manager->flush();
    }

    /**
     * @return array<string, \App\Entity\City>
     */
    private function loadCities(ObjectManager $manager): array
    {
        $cities = [];

        $city = new City('Minsk', 10);
        $manager->persist($city);
        $cities['minsk'] = $city;

        $city = new City('Brest', 5);
        $manager->persist($city);
        $cities['brest'] = $city;

        $city = new City('Grodno', 0);
        $manager->persist($city);
        $cities['grodno'] = $city;

        return $cities;
    }

    /**
     * @param array<string, \App\Entity\City> $cities
     * @param array<string, \App\Entity\SonataMediaMedia> $avatars
     */
    private function loadUsers(ObjectManager $manager, array $cities, array $avatars): void
    {
        $user = new User('Mixail', 'Ivanov', $cities['minsk']);
        $user->setMedia($avatars['avatar-1']);
        $manager->persist($user);

        $user = new User('Nikolay', 'Turchak', $cities['brest']);
        $user->setMedia($avatars['avatar-2']);
        $manager->persist($user);

        $user = new User('Eugeniy', 'Undra', $cities['grodno']);
        $user->setMedia($avatars['avatar-3']);
        $manager->persist($user);

        $user = new User('Alexey', 'Gavrilou', null);
        $manager->persist($user);
    }

    private function loadAvatars(): array
    {
        $avatars = [];

        $media = new SonataMediaMedia();
        $media->setBinaryContent(new UploadedFile(__DIR__ . '/avatars/avatar-1.jpg', 'avatar-1.jpg'));
        $media->setContext('user');
        $media->setProviderName('sonata.media.provider.image');

        $this->mediaManager->save($media);
        $avatars['avatar-1'] = $media;

        $media = new SonataMediaMedia();
        $media->setBinaryContent(new UploadedFile(__DIR__ . '/avatars/avatar-2.jpg', 'avatar-2.jpg'));
        $media->setContext('user');
        $media->setProviderName('sonata.media.provider.image');

        $this->mediaManager->save($media);
        $avatars['avatar-2'] = $media;

        $media = new SonataMediaMedia();
        $media->setBinaryContent(new UploadedFile(__DIR__ . '/avatars/avatar-3.jpg', 'avatar-3.jpg'));
        $media->setContext('user');
        $media->setProviderName('sonata.media.provider.image');

        $this->mediaManager->save($media);
        $avatars['avatar-3'] = $media;

        return $avatars;
    }
}
