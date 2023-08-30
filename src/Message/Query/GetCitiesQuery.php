<?php

declare(strict_types=1);

namespace App\Message\Query;

final readonly class GetCitiesQuery
{
    public const ORDER_BY_ID = 'id';
    public const ORDER_BY_IDX = 'idx';
    public const ORDER_BY_NAME = 'name';
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';
    private function __construct(
        public string $orderBy,
        public string $order,
    ) {}

    public static function create(mixed $orderBy, mixed $order): self
    {
        if (! in_array($orderBy, [self::ORDER_BY_ID, self::ORDER_BY_IDX, self::ORDER_BY_NAME])) {
            $orderBy = self::ORDER_BY_IDX;
        }

        if (! in_array($order, [self::ORDER_ASC, self::ORDER_DESC])) {
            $order = self::ORDER_ASC;
        }

        return new self($orderBy, $order);
    }
}
