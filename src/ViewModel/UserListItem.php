<?php

declare(strict_types=1);

namespace App\ViewModel;

use Sonata\MediaBundle\Model\Media;

final readonly class UserListItem
{
    public function __construct(
        public int          $userId,
        public string       $name,
        public string       $surName,
        public ?string      $cityName,
        public Media|string $media,
    ) {}
}
