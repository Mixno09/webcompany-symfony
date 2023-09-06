<?php

namespace App\Command;

use App\Command\Question\UserQuestionFactory;
use App\Repository\UserRepository;
use App\Validator\UserNameExists;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user:delete',
    description: 'Команда для удаления пользователя.',
)]
final class UserDeleteCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly ValidatorInterface $validator;
    private readonly UserRepository $userRepository;
    private readonly UserQuestionFactory $userQuestionFactory;
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, UserRepository $userRepository, UserQuestionFactory $userQuestionFactory)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->userQuestionFactory = $userQuestionFactory;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = $this->userQuestionFactory->createQuestion('Выберите пользователя для удаления');
        $validator = Validation::createCallable($this->validator,
            new Assert\NotBlank(),
            new UserNameExists(),
        );
        $question->setValidator($validator);
        $name = $io->askQuestion($question);
        $user = $this->userRepository->getUserByName($name);

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $io->success('Пользователь успешно удалён!');

        return Command::SUCCESS;
    }
}
