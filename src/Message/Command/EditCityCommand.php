<?php

declare(strict_types=1);

namespace App\Message\Command;

final readonly class EditCityCommand
{
    public function __construct(
        public int $cityId,
        public string $name,
        public int $idx,
    ) {}
}
