<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\SonataMediaMedia;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\TemplateType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\MediaBundle\Form\Type\MediaType;

final class SonataMediaMediaAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('batch');
        $collection->remove('create');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('providerReference')
            ->add('width')
            ->add('height')
            ->add('length')
            ->add('contentType')
            ->add('size')
            ->add('authorName')
            ->add('context')
            ->add('updatedAt')
            ->add('createdAt')
            ->add('id')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('image', null, [
                'accessor' => static fn(SonataMediaMedia $media) => $media,
                'template' => 'admin/user/list/media.html.twig',
            ])
            ->add('name')
            ->add('description')
            ->add('providerName')
            ->add('providerStatus')
            ->add('providerReference')
            ->add('width')
            ->add('height')
            ->add('contentType')
            ->add('size')
            ->add('context')
            ->add('updatedAt')
            ->add('createdAt')
            ->add('id')
            ->add(name: ListMapper::NAME_ACTIONS, fieldDescriptionOptions:[
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name')
            ->add('description')
            ->add('image', TemplateType::class, [
                'label' => 'Avatar',
                'template' => 'admin/form/image.html.twig',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('image', null, [
                'accessor' => static fn(SonataMediaMedia $media) => $media,
                'template' => 'admin/user/show/media.html.twig',
            ])
            ->add('name')
            ->add('description')
            ->add('enabled')
            ->add('providerName')
            ->add('providerReference')
            ->add('width')
            ->add('height')
            ->add('contentType')
            ->add('size')
            ->add('context')
            ->add('updatedAt')
            ->add('createdAt')
            ->add('id')
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof SonataMediaMedia
            ? $object->getName()
            : 'Image';
    }
}
