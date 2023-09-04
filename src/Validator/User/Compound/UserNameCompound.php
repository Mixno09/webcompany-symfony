<?php

declare(strict_types=1);

namespace App\Validator\User\Compound;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

#[\Attribute]
final class UserNameCompound extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length(['min' => 3, 'max' => 255]),
        ];
    }
}