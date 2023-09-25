<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\User;
use App\Message\Command\DeleteUserCommand;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DeleteUserHandler
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private MediaManagerInterface $mediaManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        #[Autowire(service: 'sonata.media.manager.media')] MediaManagerInterface $mediaManager,
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->mediaManager = $mediaManager;
    }

    public function __invoke(DeleteUserCommand $message): void
    {
        $user = $this->userRepository->findOneBy(['id' => $message->userId]);

        $oldAvatar = $user->getMedia();

        if ($oldAvatar !== null) {
            $this->mediaManager->delete($oldAvatar, false);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
