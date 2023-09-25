<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sonata\MediaBundle\Entity\BaseMedia;

#[ORM\Entity]
#[ORM\Table(name: 'media__media')]
class SonataMediaMedia extends BaseMedia
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected ?int $id = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function duplicate(): self
    {
        $media = new self();

        $media->id = $this->id;
        $media->createdAt = $this->createdAt;
        $media->updatedAt = $this->updatedAt;
        $media->name = $this->name;
        $media->description = $this->description;
        $media->enabled = $this->enabled;
        $media->providerName = $this->providerName;
        $media->providerStatus = $this->providerStatus;
        $media->providerReference = $this->providerReference;
        $media->providerMetadata = $this->providerMetadata;
        $media->width = $this->width;
        $media->height = $this->height;
        $media->length = $this->length;
        $media->copyright = $this->copyright;
        $media->authorName = $this->authorName;
        $media->context = $this->context;
        $media->cdnStatus = $this->cdnStatus;
        $media->cdnIsFlushable = $this->cdnIsFlushable;
        $media->cdnFlushIdentifier = $this->cdnFlushIdentifier;
        $media->cdnFlushAt = $this->cdnFlushAt;
        $media->binaryContent = $this->binaryContent;
        $media->previousProviderReference = $this->previousProviderReference;
        $media->contentType = $this->contentType;
        $media->size = $this->size;
        $media->galleryItems = $this->galleryItems;
        $media->category = $this->category;

        return $media;
    }
}
