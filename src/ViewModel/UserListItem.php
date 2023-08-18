<?php

declare(strict_types=1);

namespace App\ViewModel;

final readonly class UserListItem
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $surName,
        public string $cityName,
        public string $avatar,
    ) {}
}
