<?php

declare(strict_types=1);

namespace App\Message\Command;

use App\Entity\City;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class EditUserCommand
{
    public function __construct(
        public int           $userId,
        public string        $name,
        public string        $surname,
        public City          $city,
        public ?UploadedFile $file,
    ) {}
}
