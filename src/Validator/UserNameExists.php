<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class UserNameExists extends Constraint
{
    public $message = 'Пользователя с именем "{{ name }}" не существует.';
}
