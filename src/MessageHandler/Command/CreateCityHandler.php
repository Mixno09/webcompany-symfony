<?php

declare(strict_types=1);

namespace App\MessageHandler\Command;

use App\Entity\City;
use App\Message\Command\CreateCityCommand;
use Doctrine\ORM\EntityManagerInterface;

final class CreateCityHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateCityCommand $message): void
    {
        $city = new City();
        $city->setName($message->name);
        $city->setIdx($message->idx);

        $this->entityManager->persist($city);
        $this->entityManager->flush();
    }
}
