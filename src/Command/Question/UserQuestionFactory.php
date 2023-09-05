<?php

declare(strict_types=1);

namespace App\Command\Question;

use App\Repository\UserRepository;
use Symfony\Component\Console\Question\Question;

final readonly class UserQuestionFactory
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createQuestion(string $question, string|bool|int|float $default = null): Question
    {
        $question = new Question($question, $default);
        $question->setAutocompleterCallback($this->getUsers(...));

        return $question;
    }

    private function getUsers(string $name): array
    {
        return $this->userRepository->createQueryBuilder('u')
            ->select('LOWER(u.name)')
            ->where('LOWER(u.name) LIKE LOWER(:name)')
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->setMaxResults(10)
            ->setParameter('name', $name . '%')
            ->getSingleColumnResult();
    }
}
