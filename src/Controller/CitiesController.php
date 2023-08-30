<?php

namespace App\Controller;

use App\Form\CityType;
use App\Form\Dto\CityDto;
use App\Message\Query\GetCitiesQuery;
use App\MessageHandler\Command\CreateCityHandler;
use App\MessageHandler\Command\EditCityHandler;
use App\MessageHandler\Query\GetCitiesHandler;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CitiesController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CityRepository $cityRepository;

    public function __construct(EntityManagerInterface $entityManager, CityRepository $cityRepository)
    {
        $this->entityManager = $entityManager;
        $this->cityRepository = $cityRepository;
    }

    #[Route('/', name: 'cities', methods: ['GET'])]
    public function index(GetCitiesHandler $citiesHandler, Request $request): Response
    {
        $showForm = $request->query->has('form');

        $orderBy = $request->query->getString('orderBy');
        $order = $request->query->getString('order');

        $query = GetCitiesQuery::create($orderBy, $order);
        $cities = $citiesHandler($query);

        return $this->render('cities/index.html.twig', [
            'cities' => $cities,
            'data' => $query,
            'showForm' => $showForm,
        ]);
    }

    #[Route('/city/create', name: 'create_city', methods: ['GET', 'POST'])]
    public function create(CreateCityHandler $createCityHandler, Request $request): Response
    {
        $form = $this->createForm(CityType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CityDto $cityDto */
            $cityDto = $form->getData();
            $createCityCommand = $cityDto->makeCreateCityCommand();
            $createCityHandler($createCityCommand);

            return $this->redirectToRoute('cities');
        }

        return $this->render('cities/create_city.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/city/{id}/edit', name: 'edit_city', methods: ['GET', 'POST'])]
    public function edit(int $id, EditCityHandler $editCityHandler, Request $request): Response
    {
        $city = $this->cityRepository->findOneBy(['id' => $id]);
        if ($city === null) {
            throw $this->createNotFoundException();
        }

        $cityDto = CityDto::createFromCity($city);
        $form = $this->createForm(CityType::class, $cityDto);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CityDto $cityDto */
            $cityDto = $form->getData();
            $editCityCommand = $cityDto->makeEditCityCommand($city);
            $editCityHandler($editCityCommand);

            return $this->redirectToRoute('cities');
        }

        return $this->render('cities/edit_city.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/city/{id}/delete', name: 'delete_city', methods: 'POST')]
    public function delete(int $id, Request $request): Response
    {
        $city = $this->cityRepository->findOneBy(['id' => $id]);
        if ($city === null) {
            throw $this->createNotFoundException();
        }

        $token = $request->request->getString('token');
        if (! $this->isCsrfTokenValid('delete_city', $token)) {
            return new Response(status: 419);
        }

        $this->entityManager->remove($city);
        $this->entityManager->flush();

        return $this->redirectToRoute('cities');
    }
}
