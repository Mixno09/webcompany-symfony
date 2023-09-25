<?php

declare(strict_types=1);

namespace App\Message\Command;

use App\Entity\City;
use Symfony\Component\HttpFoundation\File\File;

final readonly class CreateUserCommand
{
    public function __construct(
        public string $name,
        public string $surname,
        public City   $city,
        public ?File $media = null,
    ) {
    }
}
