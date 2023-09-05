<?php

namespace App\Command;

use App\Command\Question\CityQuestionFactory;
use App\Repository\CityRepository;
use App\Validator\City\Compound\CityIdxCompound;
use App\Validator\City\Compound\CityNameCompound;
use App\Validator\CityNameExists;
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
    name: 'app:city:edit',
    description: 'Команда для редактирования города.',
)]
final class CityEditCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly ValidatorInterface $validator;
    private readonly CityRepository $cityRepository;
    private readonly CityQuestionFactory $cityQuestionFactory;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, CityRepository $cityRepository, CityQuestionFactory $cityQuestionFactory)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->cityRepository = $cityRepository;
        $this->cityQuestionFactory = $cityQuestionFactory;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = $this->cityQuestionFactory->createQuestion('Выберите город для редактирования');
        $validator = Validation::createCallable($this->validator,
            new Assert\NotBlank(),
            new CityNameExists(),
        );
        $question->setValidator($validator);
        $cityName = $io->askQuestion($question);

        $city = $this->cityRepository->getCityByName($cityName);

        $question = new Question('Введите новое имя', $city->getName());
        $validator = Validation::createCallable($this->validator,
            new CityNameCompound(),
        );
        $question->setValidator($validator);
        $cityName = $io->askQuestion($question);
        if ($cityName !== null) {
            $city->setName($cityName);
        }

        $question = new Question('Введите новый индекс сортировки', $city->getIdx());
        $validator = Validation::createCallable($this->validator,
            new CityIdxCompound(),
        );
        $question->setValidator($validator);
        $cityIdx = $io->askQuestion($question);
        if ($cityIdx !== null) {
            $city->setIdx($cityIdx);
        }

        $this->entityManager->persist($city);
        $this->entityManager->flush();

        $io->success('Город отредактирован.');

        return Command::SUCCESS;
    }
}
