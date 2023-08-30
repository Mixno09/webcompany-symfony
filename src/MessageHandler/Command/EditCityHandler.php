<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Message\Command\EditCityCommand;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

final class EditCityHandler
{
    private EntityManagerInterface $entityManager;
    private CityRepository $cityRepository;

    public function __construct(EntityManagerInterface $entityManager, CityRepository $cityRepository)
    {
        $this->entityManager = $entityManager;
        $this->cityRepository = $cityRepository;
    }

    public function __invoke(EditCityCommand $message): void
    {
        $city = $this->cityRepository->findOneBy(['id' => $message->cityId]);
        if ($city === null) {
            throw new RuntimeException("User with id ({$message->cityId}) not found.");
        }

        $city->setName($message->name);
        $city->setIdx($message->idx);

        $this->entityManager->flush();
    }
}
