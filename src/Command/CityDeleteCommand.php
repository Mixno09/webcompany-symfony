<?php

namespace App\Command;

use App\Command\Question\CityQuestionFactory;
use App\Repository\CityRepository;
use App\Validator\CityNameExists;
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
    name: 'app:city:delete',
    description: 'Команда для удаления города',
)]
final class CityDeleteCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly CityRepository $cityRepository;
    private readonly ValidatorInterface $validator;
    private readonly CityQuestionFactory $cityQuestionFactory;

    public function __construct(EntityManagerInterface $entityManager, CityRepository $cityRepository, ValidatorInterface $validator, CityQuestionFactory $cityQuestionFactory)
    {
        $this->entityManager = $entityManager;
        $this->cityRepository = $cityRepository;
        $this->validator = $validator;
        $this->cityQuestionFactory = $cityQuestionFactory;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = $this->cityQuestionFactory->createQuestion('Выберите город для удаления');
        $validator = Validation::createCallable($this->validator,
            new Assert\NotBlank(),
            new CityNameExists(),
        );
        $question->setValidator($validator);
        $cityName = $io->askQuestion($question);

        $city = $this->cityRepository->getCityByName($cityName);

        $this->entityManager->remove($city);
        $this->entityManager->flush();

        $io->success('Город успешно удалён.');

        return Command::SUCCESS;
    }
}
