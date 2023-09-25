<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Sonata\MediaBundle\Model\Media;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    private string $name = '';
    #[ORM\Column(length: 255)]
    private string $surName = '';
    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?City $city = null;
    #[ORM\OneToOne(targetEntity: SonataMediaMedia::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Media $media = null;

    public function __construct(string $name, string $surName, ?City $city)
    {
        $this->name = $name;
        $this->surName = $surName;
        $this->city = $city;
    }

    public function __toString(): string
    {
        return $this->getName() ?: '';
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

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(City $city): void
    {
        $this->city = $city;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }
}
