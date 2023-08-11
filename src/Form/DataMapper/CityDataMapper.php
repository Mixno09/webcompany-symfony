<?php

declare(strict_types=1);

namespace App\Form\DataMapper;

use App\Entity\City;
use Symfony\Component\Form\DataMapperInterface;
use Traversable;

class CityDataMapper implements DataMapperInterface
{

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (! $viewData instanceof City) {
            return;
        }

        if ($viewData->getId() === null) {
            return;
        }

        /** @var array<string, \Symfony\Component\Form\FormInterface> $forms */
        $forms = iterator_to_array($forms);

        $name = $viewData->getName();
        $forms['name']->setData($name);

        $idx = $viewData->getIdx();
        $forms['idx']->setData($idx);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        if (! $viewData instanceof City) {
            return;
        }

        /** @var array<string, \Symfony\Component\Form\FormInterface> $forms */
        $forms = iterator_to_array($forms);

        $name = $forms['name']->getData() ?? '';
        $viewData->setName($name);

        $idx = $forms['idx']->getData();
        if ($idx !== null) {
            $viewData->setIdx($idx);
        }
    }
}
