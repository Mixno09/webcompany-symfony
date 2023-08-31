<?php

namespace App\Command;

use App\Entity\City;
use App\Message\Command\CreateUserCommand as CreateUserMassageCommand;
use App\MessageHandler\Command\CreateUserHandler;
use App\Repository\CityRepository;
use App\Validator\CityNameExists;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Команда для создания нового пользователя',
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private CityRepository $cityRepository;
    private CreateUserHandler $userHandler;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, CityRepository $cityRepository, CreateUserHandler $userHandler, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->cityRepository = $cityRepository;

        parent::__construct();
        $this->userHandler = $userHandler;
        $this->validator = $validator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new Question('Введите имя'); //todo сделать через compound
        $question->setValidator($this->getNameValidation(...));
        $question->setMaxAttempts(5);
        $name = $io->askQuestion($question);

        $question = new Question('Введите фамилию');
        $question->setValidator($this->getSurNameValidation(...));
        $question->setMaxAttempts(5);
        $surname = $io->askQuestion($question);

        $question = new Question('Выберете город');
        $question->setAutocompleterCallback($this->getCities(...));

        $validation = Validation::createCallable(
            $this->validator,
            new Assert\NotBlank(),
            new CityNameExists(),
        );
        $question->setValidator($validation);
        $question->setMaxAttempts(5);
        $cityName = $io->askQuestion($question);

        $city = $this->cityRepository->getCityByName($cityName);

        $createUserCommand = new CreateUserMassageCommand($name, $surname, $city);
        ($this->userHandler)($createUserCommand);

        $io->success('Пользователь создан!');
        return Command::SUCCESS;
    }

    private function getCities(string $name): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('LOWER(c.name)')
            ->from(City::class, 'c')
            ->where('LOWER(c.name) LIKE LOWER(:name)')
            ->getQuery()
            ->setMaxResults(10)
            ->setParameter('name', $name . '%')
            ->getSingleColumnResult();
    }

    private function getNameValidation(?string $answer): string
    {
        if ($answer === null) {
            throw new RuntimeException('Имя не должно быть пустым.');
        }
        if (! is_string($answer) || strlen(trim($answer)) < 3) {
            throw new RuntimeException("Значение слишком короткое. Должно быть равно 3 символам или больше.");
        }

        return $answer;
    }

    private function getSurNameValidation(?string $answer): string
    {
        if ($answer === null) {
            throw new RuntimeException('Фамилия не должна быть пустой.');
        }
        if (! is_string($answer) || strlen(trim($answer)) < 3) {
            throw new RuntimeException("Значение слишком короткое. Должно быть равно 3 символам или больше.");
        }

        return $answer;
    }
}
