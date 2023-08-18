<?php

namespace App\Controller;

use App\Form\Dto\UserDto;
use App\Form\UserSortType;
use App\Form\UserType;
use App\Message\Command\DeleteUserCommand;
use App\Message\Query\GetUsersQuery;
use App\MessageHandler\Command\CreateUserHandler;
use App\MessageHandler\Command\DeleteUserHandler;
use App\MessageHandler\Command\EditUserHandler;
use App\MessageHandler\Query\GetUsersHandler;
use App\Repository\UserRepository;
use App\Services\UserHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'users', methods: 'GET')]
    public function index(Request $request, GetUsersHandler $getUsersHandler): Response
    {
        $showForm = $request->query->has('form');

        $query = new GetUsersQuery(GetUsersQuery::ORDER_BY_ID, GetUsersQuery::ORDER_ASC, null, null);
        $formSort = $this->createForm(UserSortType::class, data: $query, options: ['method' => 'GET']);

        $formSort->handleRequest($request);
        if ($formSort->isSubmitted() && $formSort->isValid()) {
            $query = $formSort->getData();
        }

        $users = $getUsersHandler($query);

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'formSort' => $formSort,
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
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}/user', name: 'edit_user', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, UserHelper $userHelper, EditUserHandler $editUserHandler, UserRepository $userRepository): Response
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

        $avatarWebPath = $userHelper->getAvatarWebPath($user->getAvatar());

        return $this->render('user/edit_user.html.twig', [
            'form' => $form,
            'avatarWebPath' => $avatarWebPath,
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

        $query = new GetUsersQuery(
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
