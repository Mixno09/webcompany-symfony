<?php

declare(strict_types=1);

namespace App\Form\DataMapper;

use App\Message\Query\GetUsersQuery;
use Symfony\Component\Form\DataMapperInterface;
use Traversable;

final readonly class UserSortDataMapper implements DataMapperInterface
{

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (! $viewData instanceof GetUsersQuery) {
            return;
        }

        /** @var array<string, \Symfony\Component\Form\FormInterface> $forms */
        $forms = iterator_to_array($forms);

        $forms['sort']->setData($viewData->orderBy);
        $forms['order']->setData($viewData->order);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        /** @var array<string, \Symfony\Component\Form\FormInterface> $forms */
        $forms = iterator_to_array($forms);

        $orderBy = $forms['sort']->getData() ?? GetUsersQuery::ORDER_BY_ID;
        $order = $forms['order']->getData() ?? GetUsersQuery::ORDER_ASC;
        $cityId = $forms['sortByCity']->getData()?->getId();

        $viewData = new GetUsersQuery($orderBy, $order, $cityId);
    }
}
