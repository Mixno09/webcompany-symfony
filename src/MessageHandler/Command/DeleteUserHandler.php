<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\User;
use App\Message\Command\DeleteUserCommand;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DeleteUserHandler
{
    private FileUploader $fileUploader;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(FileUploader $fileUploader, EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->fileUploader = $fileUploader;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function __invoke(DeleteUserCommand $message): void
    {
        $user = $this->userRepository->findOneBy(['id' => $message->userId]);

        $oldAvatar = $user->getAvatar();
        if ($oldAvatar !== null) {
            $this->fileUploader->delete($oldAvatar);
            $this->entityManager->remove($oldAvatar);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
