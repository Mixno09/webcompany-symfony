<?php

namespace App\Controller;

use App\Form\Dto\UserDto;
use App\Form\UserType;
use App\Message\Command\DeleteUserCommand;
use App\Message\Query\GetCitiesQuery;
use App\Message\Query\GetUsersQuery;
use App\MessageHandler\Command\CreateUserHandler;
use App\MessageHandler\Command\DeleteUserHandler;
use App\MessageHandler\Command\EditUserHandler;
use App\MessageHandler\Query\GetCitiesHandler;
use App\MessageHandler\Query\GetUsersHandler;
use App\Repository\UserRepository;
use App\ViewModel\UserListItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'users', methods: 'GET')]
    public function index(Request $request, GetUsersHandler $usersHandler, GetCitiesHandler $citiesHandler): Response
    {
        $showForm = $request->query->has('form');
        $orderBy = $request->query->getString('orderBy');
        $order = $request->query->getString('order');
        $cityId = $request->query->getInt('cityId');

        $usersQuery = GetUsersQuery::create($orderBy, $order, cityId: $cityId);
        $users = $usersHandler($usersQuery);

        $citiesQuery = GetCitiesQuery::create(GetCitiesQuery::ORDER_BY_IDX, GetCitiesQuery::ORDER_ASC);
        $cities = $citiesHandler($citiesQuery);

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'cities' => $cities,
            'data' => $usersQuery,
            'showForm' => $showForm,
        ]);
    }

    #[Route('/create/user', name: 'create_user', methods: ['GET', 'POST'])]
    public function create(Request $request, CreateUserHandler $userHandler): Response
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Form\Dto\UserDto $userDto */
            $userDto = $form->getData();
            $createUserCommand = $userDto->makeCreateUserCommand();
            $userHandler($createUserCommand);

            return $this->redirectToRoute('users');
        }

        return $this->render('user/create_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}/user', name: 'edit_user', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EditUserHandler $editUserHandler, UserRepository $userRepository, #[Autowire(param: 'user_avatar_placeholder')] string $userAvatarPlaceholder): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        if ($user === null) {
            throw $this->createNotFoundException();
        }

        $userDto = UserDto::createFromUser($user);
        $form = $this->createForm(UserType::class, $userDto);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserDto $userDto */
            $userDto = $form->getData();

            $editUserCommand = $userDto->makeEditUserCommand($user);
            $editUserHandler($editUserCommand);

            return $this->redirectToRoute('edit_user', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('user/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}/user', name: 'delete_user', methods: 'POST')]
    public function delete(int $id, Request $request, DeleteUserHandler $deleteUserHandler, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        if ($user === null) {
            throw $this->createNotFoundException();
        }

        $token = $request->request->getString('token');
        if (! $this->isCsrfTokenValid('delete_user', $token)) {
            return new Response(status: 419);
        }

        $deleteUserCommand = new DeleteUserCommand($user->getId());
        $deleteUserHandler($deleteUserCommand);

        return $this->redirectToRoute('users');
    }

    #[Route('/search', name: 'search_user', methods: 'GET')]
    public function search(Request $request, GetUsersHandler $usersHandler): Response
    {
        $searchText = $request->query->get('q');

        if (! is_string($searchText) || trim($searchText) === '') {
            return $this->render('user/search.html.twig');
        }

        $query = GetUsersQuery::create(
            GetUsersQuery::ORDER_BY_ID,
            GetUsersQuery::ORDER_ASC,
            $searchText,
        );

        $users = $usersHandler($query);

        return $this->render('user/search.html.twig', [
            'users' => $users,
        ]);
    }
}
