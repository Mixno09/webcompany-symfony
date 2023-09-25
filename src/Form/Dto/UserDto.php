<?php

declare(strict_types=1);

namespace App\Form\Dto;

use App\Entity\City;
use App\Entity\User;
use App\Message\Command\CreateUserCommand;
use App\Message\Command\EditUserCommand;
use App\Validator\User\Compound as AssertCompound;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class UserDto
{
    #[Assert\NotBlank]
    #[AssertCompound\UserNameCompound]
    public ?string $name = null;
    #[Assert\NotBlank]
    #[AssertCompound\UserSurNameCompound]
    public ?string $surName = null;
    #[Assert\NotBlank]
    public ?City $city = null;
    #[Assert\File(
        maxSize: '1024K',
        mimeTypes: ['image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png'],
        extensions: ['png', 'jpeg', 'jpg'],
        extensionsMessage: "Недопустимое расширение файла {{ extension }}. Разрешенные расширения {{ extensions }}.",
    )]
    public ?UploadedFile $media = null;

    public static function createFromUser(User $user): self
    {
        $userDto = new self();
        $userDto->name = $user->getName();
        $userDto->surName = $user->getSurName();
        $userDto->city = $user->getCity();

        return $userDto;
    }

    public function makeCreateUserCommand(): CreateUserCommand
    {
        return new CreateUserCommand($this->name, $this->surName, $this->city, $this->media);
    }

    public function makeEditUserCommand(User $user): EditUserCommand
    {
        return new EditUserCommand($user->getId(), $this->name, $this->surName, $this->city, $this->media);
    }
}
