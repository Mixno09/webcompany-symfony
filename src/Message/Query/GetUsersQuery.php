<?php

namespace App\Message\Query;

final readonly class GetUsersQuery
{
    public const ORDER_BY_ID = 'id';
    public const ORDER_BY_NAME = 'name';
    public const ORDER_BY_SURNAME = 'surname';
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';
    private function __construct(
        public string $orderBy,
        public string $order,
        public ?string $conditions = null,
        public ?int $cityId = null,
    ) {}

    public static function create(mixed $orderBy, mixed $order, ?string $conditions = null, ?int $cityId = null): self
    {
        if (! in_array($orderBy, [self::ORDER_BY_ID, self::ORDER_BY_NAME, self::ORDER_BY_SURNAME], true)) {
            $orderBy = self::ORDER_BY_ID;
        }

        if (! in_array($order, [self::ORDER_ASC, self::ORDER_DESC], true)) {
            $order = self::ORDER_ASC;
        }

        if ($cityId === 0) {
            $cityId = null;
        }

        return new self($orderBy, $order, $conditions, $cityId);
    }
}
