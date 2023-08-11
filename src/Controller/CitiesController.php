<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CitySortType;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CitiesController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CityRepository $cityRepository;

    public function __construct(EntityManagerInterface $entityManager, CityRepository $cityRepository)
    {
        $this->entityManager = $entityManager;
        $this->cityRepository = $cityRepository;
    }

    #[Route('/', name: 'cities', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $showForm = $request->query->has('form');
        $formSort = $this->createForm(CitySortType::class);

        $orderBy = $request->query->getString('sort');
        $order = $request->query->getString('order');

        $repository = $this->cityRepository;

        if (isset($orderBy) && isset($order)) {
            $cities = $repository->findAllCity($orderBy, $order);

            return $this->render('cities/index.html.twig', [
                'cities' => $cities,
                'formSort' => $formSort,
                'showForm' => $showForm,
            ]);
        }

        $cities = $repository->findAllCity();

        return $this->render('cities/index.html.twig', [
            'cities' => $cities,
            'formSort' => $formSort,
            'showForm' => $showForm,
        ]);
    }

    #[Route('/create/city', name: 'create_city', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $city = new City();

        $form = $this->createForm(CityType::class, $city);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($city);
            $this->entityManager->flush();

            return $this->redirectToRoute('cities');
        }

        return $this->render('cities/create_city.html.twig', [
            'form' => $form,
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

    #[Route('/city/{id}/edit', name: 'edit_city', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $city = $this->cityRepository->findOneBy(['id' => $id]);
        if ($city === null) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CityType::class, $city);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($city);
            $this->entityManager->flush();

            return $this->redirectToRoute('cities');
        }

        return $this->render('cities/edit_city.html.twig', [
            'form' => $form
        ]);
    }
}
