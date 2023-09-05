<?php

namespace App\Command;

use App\Command\Question\CityQuestionFactory;
use App\Command\Question\UserQuestionFactory;
use App\Repository\CityRepository;
use App\Repository\UserRepository;
use App\Validator\CityNameExists;
use App\Validator\User\Compound\UserNameCompound;
use App\Validator\User\Compound\UserSurNameCompound;
use App\Validator\UserNameExists;
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
    name: 'app:user:edit',
    description: 'Команда для редактирования пользователя.',
)]
final class UserEditCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly ValidatorInterface $validator;
    private readonly UserRepository $userRepository;
    private readonly CityRepository $cityRepository;
    private readonly CityQuestionFactory $cityQuestionFactory;
    private readonly UserQuestionFactory $userQuestionFactory;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, UserRepository $userRepository, CityRepository $cityRepository, CityQuestionFactory $cityQuestionFactory, UserQuestionFactory $userQuestionFactory)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->cityRepository = $cityRepository;
        $this->cityQuestionFactory = $cityQuestionFactory;
        $this->userQuestionFactory = $userQuestionFactory;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = $this->userQuestionFactory->createQuestion('Выберите пользователя для редактирования');
        $validator = Validation::createCallable($this->validator,
            new Assert\NotBlank(),
            new UserNameExists(),
        );
        $question->setValidator($validator);
        $userName = $io->askQuestion($question);

        $user = $this->userRepository->getUserByName($userName);

        $question = new Question('Введите новое имя', $user->getName());
        $validator = Validation::createCallable($this->validator,
            new UserNameCompound(),
        );
        $question->setValidator($validator);
        $name = $io->askQuestion($question);
        if ($name !== null) {
            $user->setName($name);
        }

        $question = new Question('Введите новую фамилию', $user->getSurName());
        $validator = Validation::createCallable($this->validator,
            new UserSurNameCompound(),
        );
        $question->setValidator($validator);
        $surName = $io->askQuestion($question);
        if ($surName !== null) {
            $user->setSurName($surName);
        }

        $question = $this->cityQuestionFactory->createQuestion('Выберете новый город');
        $validator = Validation::createCallable($this->validator,
            new CityNameExists(),
        );
        $question->setValidator($validator);
        $cityName = $io->askQuestion($question);
        if ($cityName !== null) {
            $city = $this->cityRepository->getCityByName($cityName);
            $user->setCity($city);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Пользователь изменён!');

        return Command::SUCCESS;
    }
}
