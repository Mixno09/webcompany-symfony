<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Message\Command\EditUserCommand;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EditUserHandler
{
    private EntityManagerInterface $entityManager;
    private FileUploader $fileUploader;
    private UserRepository $userRepository;
    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->userRepository = $userRepository;
    }

    public function __invoke(EditUserCommand $message): void
    {
        $user = $this->userRepository->findOneBy(['id' => $message->userId]);
        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $user->setName($message->name);
        $user->setSurName($message->surname);
        $user->setCity($message->city);

        $oldAvatar = null;
        if ($message->file !== null) {
            $avatar = $this->fileUploader->upload($message->file);
            $this->entityManager->persist($avatar);

            $oldAvatar = $user->getAvatar();
            $user->setAvatar($avatar);
        }

        $this->entityManager->flush();

        if ($oldAvatar !== null) {
            $this->fileUploader->delete($oldAvatar);

            $this->entityManager->remove($oldAvatar);
            $this->entityManager->flush();
        }
    }
}
