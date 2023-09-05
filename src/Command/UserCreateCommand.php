<?php

namespace App\Command;

use App\Command\Question\CityQuestionFactory;
use App\Entity\City;
use App\Message\Command\CreateUserCommand as CreateUserMassageCommand;
use App\MessageHandler\Command\CreateUserHandler;
use App\Repository\CityRepository;
use App\Validator\CityNameExists;
use App\Validator\User\Compound\UserNameCompound;
use App\Validator\User\Compound\UserSurNameCompound;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Команда для создания нового пользователя',
)]
final class UserCreateCommand extends Command
{
    private readonly CityRepository $cityRepository;
    private readonly CreateUserHandler $userHandler;
    private readonly ValidatorInterface $validator;
    private readonly CityQuestionFactory $cityQuestionFactory;

    public function __construct(CityRepository $cityRepository, CreateUserHandler $userHandler, ValidatorInterface $validator, CityQuestionFactory $cityQuestionFactory)
    {
        $this->cityRepository = $cityRepository;
        $this->userHandler = $userHandler;
        $this->validator = $validator;
        $this->cityQuestionFactory = $cityQuestionFactory;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new Question('Введите имя');
        $validator = Validation::createCallable($this->validator,
            new Assert\NotBlank(),
            new UserNameCompound(),
        );
        $question->setValidator($validator);
        $question->setMaxAttempts(5);
        $name = $io->askQuestion($question);

        $question = new Question('Введите фамилию');
        $validator = Validation::createCallable($this->validator,
            new Assert\NotBlank(),
            new UserSurNameCompound(),
        );
        $question->setValidator($validator);
        $question->setMaxAttempts(5);
        $surname = $io->askQuestion($question);

        $question = $this->cityQuestionFactory->createQuestion('Выберете город');

        $validator = Validation::createCallable(
            $this->validator,
            new Assert\NotBlank(),
            new CityNameExists(),
        );
        $question->setValidator($validator);
        $question->setMaxAttempts(5);
        $cityName = $io->askQuestion($question);

        $city = $this->cityRepository->getCityByName($cityName);

        $createUserCommand = new CreateUserMassageCommand($name, $surname, $city);
        ($this->userHandler)($createUserCommand);

        $io->success('Пользователь создан!');
        return Command::SUCCESS;
    }
}
