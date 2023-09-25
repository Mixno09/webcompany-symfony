<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\City;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CityAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('batch');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', TextType::class)
            ->add('idx', IntegerType::class)
        ;
    }
    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('name')
            ->add('idx')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', fieldDescriptionOptions: [
                'label' => 'Название города',
            ])
            ->add('idx', fieldDescriptionOptions: [
                'label' => 'Индекс',
            ])
            ->add(name: ListMapper::NAME_ACTIONS, fieldDescriptionOptions:[
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
                'label' => 'Действия',
            ])
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof City
            ? $object->getName()
            : 'City';
    }
}
