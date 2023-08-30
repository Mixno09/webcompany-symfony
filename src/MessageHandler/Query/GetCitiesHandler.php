<?php

declare(strict_types=1);

namespace App\MessageHandler\Query;

use App\Message\Query\GetCitiesQuery;
use App\Repository\CityRepository;

final class GetCitiesHandler
{
    private CityRepository $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    public function __invoke(GetCitiesQuery $message): array
    {
        $orderBy = match ($message->orderBy) {
            $message::ORDER_BY_ID => 'c.id',
            $message::ORDER_BY_NAME => 'c.name',
            default => 'c.idx',
        };
        $order = match ($message->order) {
            $message::ORDER_DESC => 'DESC',
            default => 'ASC',
        };

        return $this->cityRepository->createQueryBuilder('c')
            ->orderBy($orderBy, $order)
            ->getQuery()
            ->getResult();
    }
}
