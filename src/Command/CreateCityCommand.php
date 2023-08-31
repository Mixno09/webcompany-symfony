<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\Command\CreateCityCommand as CreateCityMessageCommand;
use App\MessageHandler\Command\CreateCityHandler;
use Closure;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-city',
    description: 'Команда для создания нового города',
)]
class CreateCityCommand extends Command
{
    private CreateCityHandler $cityHandler;

    public function __construct(CreateCityHandler $cityHandler)
    {
        $this->cityHandler = $cityHandler;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new Question('Введите имя');
        $question->setValidator(Closure::fromCallable([$this, 'getNameValidation']));
        $question->setMaxAttempts(5);
        $name = $io->askQuestion($question);

        $question = new Question('Введите индекс сортировки');
        $question->setValidator(Closure::fromCallable([$this, 'getIdxValidation']));
        $question->setMaxAttempts(5);
        $idx = $io->askQuestion($question);

        $createCityCommand = new CreateCityMessageCommand($name, $idx);
        ($this->cityHandler)($createCityCommand);

        $io->success('Город успешно создан!');

        return Command::SUCCESS;
    }

    private function getNameValidation(?string $answer): string //todo
    {
        if ($answer === null) {
            throw new RuntimeException('Имя не должно быть пустым.');
        }
        if (! is_string($answer) || strlen(trim($answer)) < 3) {
            throw new RuntimeException("Значение слишком короткое. Должно быть равно 3 символам или больше.");
        }

        return $answer;
    }

    private function getIdxValidation(?int $answer): int
    {
        if ($answer === null) {
            throw new RuntimeException('Индекс не должен быть пустым.');
        }
        if (! is_int($answer) || $answer < 0) {
            throw new RuntimeException("Значение должно быть положительным числом.");
        }
        if (! is_int($answer) || $answer > 65535) {
            throw new RuntimeException("Значение должно быть больше 65535.");
        }

        return $answer;
    }
}
