<?php

declare(strict_types=1);

namespace App\Form\Dto;

use App\Entity\City;
use App\Message\Command\CreateCityCommand;
use App\Message\Command\EditCityCommand;
use App\Validator\City\Compound as AssertCompound;
use Symfony\Component\Validator\Constraints as Assert;

final class CityDto
{
    #[Assert\NotBlank]
    #[AssertCompound\CityNameCompound]
    public string $name;
    #[Assert\NotBlank]
    #[AssertCompound\CityIdxCompound]
    public int $idx;

    public static function createFromCity(City $city): self
    {
        $cityDto = new self();
        $cityDto->name = $city->getName();
        $cityDto->idx = $city->getIdx();

        return $cityDto;
    }

    public function makeCreateCityCommand(): CreateCityCommand
    {
        return new CreateCityCommand($this->name, $this->idx);
    }

    public function makeEditCityCommand(City $city): EditCityCommand
    {
        return new EditCityCommand($city->getId(), $this->name, $this->idx);
    }
}
