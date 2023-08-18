<?php

namespace App\Message\Query;

final readonly class GetUsersQuery
{
    public const ORDER_BY_ID = 'id';
    public const ORDER_BY_NAME = 'name';
    public const ORDER_BY_SURNAME = 'surname';
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';
    public function __construct(
        public string $orderBy,
        public string $order,
        public ?string $conditions = null,
        public ?int $cityId = null,
    ) {}
}
