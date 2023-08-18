<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    private string $name;
    #[ORM\Column(length: 255)]
    private string $mimeType;
    #[ORM\Column(options: ['unsigned' => true])]
    private int $size;

    public function __construct(string $name, string $mimeType, int $size)
    {
        $this->name = $name;
        $this->mimeType = $mimeType;
        $this->size = $size;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
