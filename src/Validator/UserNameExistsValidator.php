<?php

namespace App\Validator;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UserNameExistsValidator extends ConstraintValidator
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /* @var \App\Validator\UserNameExists $constraint */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (! $constraint instanceof UserNameExists) {
            throw new UnexpectedTypeException($constraint, UserNameExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $user = $this->userRepository->getUserByName($value);

        if (! $user instanceof User) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $value)
                ->addViolation();
        }
    }
}
