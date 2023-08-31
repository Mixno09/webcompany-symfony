<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class CityNameExists extends Constraint
{
    public string $message = "Города с именем {{ name }} не существует";
}
