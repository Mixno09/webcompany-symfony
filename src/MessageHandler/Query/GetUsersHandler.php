<?php

namespace App\MessageHandler\Query;

use App\Message\Query\GetUsersQuery;
use App\Repository\FileRepository;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use App\ViewModel\UserListItem;
use Doctrine\ORM\Query;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use function array_column;
use function array_filter;
use function array_key_exists;

final readonly class GetUsersHandler
{
    private FileRepository $fileRepository;
    private UserRepository $userRepository;
    private FileUploader $fileUploader;
    private string $userAvatarPlaceholder;

    public function __construct(
        FileRepository                                       $fileRepository,
        UserRepository                                       $userRepository,
        FileUploader                                         $fileUploader,
        #[Autowire(param: 'user_avatar_placeholder')] string $userAvatarPlaceholder,
    ) {
        $this->fileRepository = $fileRepository;
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
            ->select(['u.id', 'u.name', 'u.surName', 'c.name AS cityName', 'IDENTITY(u.avatar) AS avatarId'])
            ->leftJoin('u.city', 'c')
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

        $result = $queryBuilder->getQuery()->execute();

        $files = $this->getFiles(array_column($result, 'avatarId'));

        $items = [];
        foreach ($result as $row) {
            $avatar = (array_key_exists($row['avatarId'], $files) ? $files[$row['avatarId']] : null);
            $avatarWebPath = ($avatar !== null ? $this->fileUploader->getWebPath($avatar) : $this->userAvatarPlaceholder);
            $items[] = new UserListItem($row['id'], $row['name'], $row['surName'], $row['cityName'], $avatarWebPath);
        }

        return $items;
    }

    /**
     * @return \App\Entity\File[]
     */
    public function getFiles(array $fileIds): array
    {
        if (count($fileIds) === 0) {
            return [];
        }

        $fileIds = array_filter($fileIds, 'is_int');

        return $this->fileRepository->createQueryBuilder('f', 'f.id')
            ->where('f.id IN (:fileIds)')
            ->setParameter('fileIds', $fileIds)
            ->getQuery()
            ->setHint(Query::HINT_READ_ONLY, true)
            ->execute();
    }
}
