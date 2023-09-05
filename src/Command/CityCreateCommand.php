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
use Symfony\Component\Validator\Constraints as Assert;

#[AsCommand(
    name: 'app:city:create',
    description: 'Команда для создания нового города',
)]
final class CityCreateCommand extends Command
{
    private readonly CreateCityHandler $cityHandler;
    private readonly ValidatorInterface $validator;

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
        $validator = Validation::createCallable($this->validator,
            new Assert\NotBlank(),
            new CityNameCompound(),
        );
        $question->setValidator($validator);
        $question->setMaxAttempts(5);
        $name = $io->askQuestion($question);

        $question = new Question('Введите индекс сортировки');
        $validator = Validation::createCallable($this->validator,
            new Assert\NotBlank(),
            new CityIdxCompound(),
        );
        $question->setValidator($validator);
        $question->setMaxAttempts(5);
        $idx = $io->askQuestion($question);

        $createCityCommand = new CreateCityMessageCommand($name, (int) $idx);
        ($this->cityHandler)($createCityCommand);

        $io->success('Город успешно создан!');

        return Command::SUCCESS;
    }
}
