<?php

declare(strict_types=1);

namespace App\Command\Question;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Component\Console\Question\Question;

final readonly class CityQuestionFactory
{
    private CityRepository $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    public function createQuestion(string $question, string|bool|int|float $default = null): Question
    {
        $question = new Question($question, $default);
        $question->setAutocompleterCallback($this->getCities(...));

        return $question;
    }

    private function getCities(string $name): array
    {
        return $this->cityRepository->createQueryBuilder('c')
            ->select('LOWER(c.name)')
            ->where('LOWER(c.name) LIKE LOWER(:name)')
            ->getQuery()
            ->setMaxResults(10)
            ->setParameter('name', $name . '%')
            ->getSingleColumnResult();
    }
}
