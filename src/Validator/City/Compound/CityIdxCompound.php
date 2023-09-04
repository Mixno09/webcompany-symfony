<?php

declare(strict_types=1);

namespace App\Validator\City\Compound;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

#[\Attribute]
class CityIdxCompound extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\Type('numeric'),
            new Assert\Range(['min' => 0, 'max' => 65535]),
        ];
    }
}
