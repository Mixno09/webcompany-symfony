<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private string $name = '';

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 65535)]
    private int $idx = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIdx(): int
    {
        return $this->idx;
    }

    public function setIdx(int $idx): static
    {
        $this->idx = $idx;

        return $this;
    }
}
