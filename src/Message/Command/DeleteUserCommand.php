<?php

declare(strict_types=1);

namespace App\Message\Command;

final readonly class DeleteUserCommand
{
    public function __construct(
        public int $userId,
    ) {}
}
