<?php

declare(strict_types=1);

namespace App\Form\DataMapper;

use App\Entity\City;
use App\Entity\User;
use Sonata\MediaBundle\Model\Media;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Traversable;

final readonly class UserAdminDataMapper implements DataMapperInterface
{
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (null === $viewData) {
            return;
        }

        if (! $viewData instanceof User) {
            throw new UnexpectedTypeException($viewData, User::class);
        }

        /** @var \App\Entity\User $viewData */
        /** @var \Symfony\Component\Form\FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $name = $viewData->getName();
        $forms['name']->setData($name);
        $surName = $viewData->getSurName();
        $forms['surName']->setData($surName);
        $city = $viewData->getCity();
        $forms['city']->setData($city);
        $media = $viewData->getMedia();
        $forms['media']->setData($media);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        if (! $viewData instanceof User) {
            throw new UnexpectedTypeException($viewData, User::class);
        }

        /** @var \App\Entity\User $viewData */
        /** @var \Symfony\Component\Form\FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $name = $forms['name']->getData();
        if (is_string($name)) {
            $viewData->setName($name);
        }

        $surName = $forms['surName']->getData();
        if (is_string($surName)) {
            $viewData->setSurName($surName);
        }

        $city = $forms['city']->getData();
        if ($city instanceof City) {
            $viewData->setCity($city);
        }

        $media = $forms['media']->getData();
        if ($media instanceof Media || $media === null) {
            $viewData->setMedia($media);
        }
    }
}
