<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\File;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class UserHelper
{
    private FileUploader $fileUploader;
    private string $userAvatarPlaceholder;

    public function __construct(
        FileUploader $fileUploader,
        #[Autowire(param: 'user_avatar_placeholder')] string $userAvatarPlaceholder,
    ) {
        $this->fileUploader = $fileUploader;
        $this->userAvatarPlaceholder = $userAvatarPlaceholder;
    }

    public function getAvatarWebPath(?File $avatar): string
    {
        if ($avatar === null) {
            return $this->userAvatarPlaceholder;
        }

        return $this->fileUploader->getWebPath($avatar);
    }
}
