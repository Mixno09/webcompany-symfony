<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\SonataMediaMedia;
use App\Message\Command\EditUserCommand;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EditUserHandler
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private MediaManagerInterface $mediaManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        #[Autowire(service: 'sonata.media.manager.media')] MediaManagerInterface $mediaManager)
    {
        $this->mediaManager = $mediaManager;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function __invoke(EditUserCommand $message): void
    {
        $user = $this->userRepository->findOneBy(['id' => $message->userId]);
        if ($user === null) {
            throw new RuntimeException("User with id ({$message->userId}) not found.");
        }

        $user->setName($message->name);
        $user->setSurName($message->surname);
        $user->setCity($message->city);

        if ($message->media !== null) {
            $media = new SonataMediaMedia();
            $media->setBinaryContent($message->media);
            $media->setProviderName('sonata.media.provider.image');
            $media->setContext('user');
            $this->mediaManager->save($media, false);

            $user->setMedia($media);
        }

        $this->entityManager->flush();
    }
}
