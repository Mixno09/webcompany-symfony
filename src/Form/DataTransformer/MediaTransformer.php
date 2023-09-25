<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\SonataMediaMedia;
use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

final class MediaTransformer implements DataTransformerInterface
{
    private MediaManagerInterface $mediaManager;
    private array $options;

    public function __construct(MediaManagerInterface $mediaManager, array $options)
    {
        $this->mediaManager = $mediaManager;
        $this->options = $options;
    }

    public function transform(mixed $value): ?Media
    {
        if (null === $value) {
            return null;
        }

        if (! $value instanceof SonataMediaMedia) {
            return null;
        }

        if ($this->options['new_on_update'] === false) {
            return $value;
        }

        return $value->duplicate();
    }

    public function reverseTransform(mixed $value): ?Media
    {
        if (null === $value) {
            return null;
        }

        if (! $value instanceof SonataMediaMedia) {
            return $value;
        }

        if ($value->getId() === null) {
            return $value;
        }

        if ($this->options['new_on_update'] === false) {
            return $value;
        }

        return $this->mediaManager->find($value->getId());
    }
}
