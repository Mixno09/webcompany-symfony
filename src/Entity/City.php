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
    private string $name = '';

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true])]
    private int $idx = 0;

    public function __construct(string $name, int $idx = 0)
    {
        $this->name = $name;
        $this->idx = $idx;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setIdx(int $idx): void
    {
        $this->idx = $idx;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIdx(): int
    {
        return $this->idx;
    }
}
