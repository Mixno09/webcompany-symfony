<?php

namespace App\Validator;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CityNameExistsValidator extends ConstraintValidator
{
    private CityRepository $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    /* @var \App\Validator\CityNameExists $constraint */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof CityNameExists) {
            throw new UnexpectedTypeException($constraint, CityNameExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $city = $this->cityRepository->getCityByName($value);

        if (! $city instanceof City) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $value)
                ->addViolation();
        }
    }
}
