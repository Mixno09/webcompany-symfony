<?php

declare(strict_types=1);

namespace App\Form\Dto;

use App\Entity\City;
use App\Entity\User;
use App\Message\Command\CreateUserCommand;
use App\Message\Command\EditUserCommand;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class UserDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $surName = null;
    #[Assert\NotBlank]
    public ?City $city = null;
    #[Assert\File(maxSize: '1024k', extensions: ['jpg', 'png'])]
    public ?UploadedFile $file = null;

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
        return new CreateUserCommand($this->name, $this->surName, $this->city, $this->file);
    }

    public function makeEditUserCommand(User $user): EditUserCommand
    {
        return new EditUserCommand($user->getId(), $this->name, $this->surName, $this->city, $this->file);
    }
}
