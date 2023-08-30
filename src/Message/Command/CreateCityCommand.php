<?php

declare(strict_types=1);

namespace App\Message\Command;

final readonly class CreateCityCommand
{
    public function __construct(
        public string $name,
        public int $idx
    ) {}
}
