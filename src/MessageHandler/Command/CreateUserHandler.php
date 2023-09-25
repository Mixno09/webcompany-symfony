<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\SonataMediaMedia;
use App\Entity\User;
use App\Message\Command\CreateUserCommand;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class CreateUserHandler
{
    private EntityManagerInterface $entityManager;
    private MediaManagerInterface $mediaManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        #[Autowire(service: 'sonata.media.manager.media')] MediaManagerInterface $mediaManager,
    ) {
        $this->entityManager = $entityManager;
        $this->mediaManager = $mediaManager;
    }

    public function __invoke(CreateUserCommand $message): void
    {
        $user = new User($message->name, $message->surname, $message->city);
        if ($message->media !== null) {
            $media = new SonataMediaMedia();
            $media->setBinaryContent($message->media);
            $media->setProviderName('sonata.media.provider.image');
            $media->setContext('user');
            $this->mediaManager->save($media, false);

            $user->setMedia($media);
        }
        $this->entityManager->persist($user);

        $this->entityManager->flush();
    }
}
