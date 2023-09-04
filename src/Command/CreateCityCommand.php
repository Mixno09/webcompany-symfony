<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\Command\CreateCityCommand as CreateCityMessageCommand;
use App\MessageHandler\Command\CreateCityHandler;
use App\Validator\City\Compound\CityIdxCompound;
use App\Validator\City\Compound\CityNameCompound;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-city',
    description: 'Команда для создания нового города',
)]
final class CreateCityCommand extends Command
{
    private CreateCityHandler $cityHandler;
    private ValidatorInterface $validator;

    public function __construct(CreateCityHandler $cityHandler, ValidatorInterface $validator)
    {
        $this->cityHandler = $cityHandler;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new Question('Введите имя');
        $validator = Validation::createCallable($this->validator, new CityNameCompound());
        $question->setValidator($validator);
        $question->setMaxAttempts(5);
        $name = $io->askQuestion($question);

        $question = new Question('Введите индекс сортировки');
        $validator = Validation::createCallable($this->validator, new CityIdxCompound());
        $question->setValidator($validator);
        $question->setMaxAttempts(5);
        $idx = $io->askQuestion($question);

        $createCityCommand = new CreateCityMessageCommand($name, (int) $idx);
        ($this->cityHandler)($createCityCommand);

        $io->success('Город успешно создан!');

        return Command::SUCCESS;
    }
}
