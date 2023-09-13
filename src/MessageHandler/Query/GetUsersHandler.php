<?php

namespace App\MessageHandler\Query;

use App\Entity\User;
use App\Message\Query\GetUsersQuery;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use App\ViewModel\UserListItem;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GetUsersHandler
{
    private UserRepository $userRepository;
    private FileUploader $fileUploader;
    private string $userAvatarPlaceholder;

    public function __construct(
        UserRepository                                       $userRepository,
        FileUploader                                         $fileUploader,
        #[Autowire(param: 'user_avatar_placeholder')] string $userAvatarPlaceholder,
    ) {
        $this->userRepository = $userRepository;
        $this->fileUploader = $fileUploader;
        $this->userAvatarPlaceholder = $userAvatarPlaceholder;
    }

    /**
     * @return \App\ViewModel\UserListItem[]
     */
    public function __invoke(GetUsersQuery $message): array
    {
        $orderBy = match ($message->orderBy) {
            $message::ORDER_BY_NAME => 'u.name',
            $message::ORDER_BY_SURNAME => 'u.surName',
            default => 'u.id',
        };

        $order = match ($message->order) {
            $message::ORDER_DESC => 'DESC',
            default => 'ASC',
        };

        $queryBuilder = $this->userRepository->createQueryBuilder('u')
            ->orderBy($orderBy, $order);

        if ($message->conditions !== null) {
            $queryBuilder
                ->andWhere('u.name LIKE :query OR u.surName LIKE :query')
                ->setParameter('query', '%' . $message->conditions . '%');
        }

        if ($message->cityId !== null) {
            $queryBuilder
                ->andWhere('IDENTITY(u.city) = :cityId')
                ->setParameter('cityId', $message->cityId);
        }

        $query = $queryBuilder->getQuery();
        $query->setFetchMode(User::class, 'city', ClassMetadataInfo::FETCH_EAGER);
        $query->setFetchMode(User::class, 'avatar', ClassMetadataInfo::FETCH_EAGER);
        /** @var User[] $result */
        $result = $query->getResult();

        $items = [];
        foreach ($result as $user) {
            $avatar = $user->getAvatar();
            $avatarWebPath = ($avatar !== null ? $this->fileUploader->getWebPath($avatar) : $this->userAvatarPlaceholder);
            $items[] = new UserListItem($user->getId(), $user->getName(), $user->getSurName(), $user->getCity()?->getName(), $avatarWebPath);
        }

        return $items;
    }
}
