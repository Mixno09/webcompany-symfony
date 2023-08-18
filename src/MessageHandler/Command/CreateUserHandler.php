<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\User;
use App\Message\Command\CreateUserCommand;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreateUserHandler
{
    private FileUploader $fileUploader;
    private EntityManagerInterface $entityManager;
    public function __construct(
        FileUploader           $fileUploader,
        EntityManagerInterface $entityManager,
    ) {
        $this->fileUploader = $fileUploader;
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateUserCommand $message): void
    {
        $user = new User($message->name, $message->surname, $message->city);
        $this->entityManager->persist($user);

        if ($message->file !== null) {
            $avatar = $this->fileUploader->upload($message->file);
            $this->entityManager->persist($avatar);

            $user->setAvatar($avatar);
        }

        $this->entityManager->flush();
    }
}
