<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    private string $name;
    #[ORM\Column(length: 255)]
    private string $surName;
    #[ORM\ManyToOne(targetEntity: City::class)]
    private City $city;
    #[ORM\OneToOne(targetEntity: File::class)]
    private ?File $avatar = null;

    public function __construct(string $name, string $surName, City $city)
    {
        $this->name = $name;
        $this->surName = $surName;
        $this->city = $city;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSurName(): string
    {
        return $this->surName;
    }

    public function setSurName(string $surName): void
    {
        $this->surName = $surName;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): void
    {
        $this->city = $city;
    }

    public function getAvatar(): ?File
    {
        return $this->avatar;
    }

    public function setAvatar(File $avatar): void
    {
        $this->avatar = $avatar;
    }
}
