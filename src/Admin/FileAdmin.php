<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\File;
use App\Services\UserHelper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

final class FileAdmin extends AbstractAdmin implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('batch');
        $collection->remove('create');
        $collection->remove('edit');
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('name');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('mimeType')
            ->add('size')
            ->add('image', FieldDescriptionInterface::TYPE_STRING, [
                'accessor' => function ($subject) {
                    return $this->getUserHelper()->getAvatarWebPath($subject);
                },
                'template' => 'admin/avatar/image.html.twig',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Имя')
                ->add('name')
                ->add('image', FieldDescriptionInterface::TYPE_STRING, [
                    'accessor' => function ($subject) {
                        return $this->getUserHelper()->getAvatarWebPath($subject);
                    },
                    'template' => 'admin/user/image.html.twig',
                ])
            ->end()
            ->with('Тип')
                ->add('mimeType')
            ->end()
            ->with('Размер')
                ->add('size')
            ->end()
        ;
    }

    #[SubscribedService]
    private function getUserHelper(): UserHelper
    {
        return $this->container->get(__METHOD__);
    }

    public function toString(object $object): string
    {
        return $object instanceof File
            ? $object->getName()
            : 'File';
    }
}
