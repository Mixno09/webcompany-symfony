<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $surName = '';

    #[ORM\Column]
    private ?int $cityId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileName = null;

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

    public function getSurName(): string
    {
        return $this->surName;
    }

    public function setSurName(string $surName): static
    {
        $this->surName = $surName;

        return $this;
    }

    public function getCityId(): ?int
    {
        return $this->cityId;
    }

    public function setCityId(int $cityId): static
    {
        $this->cityId = $cityId;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }
}
