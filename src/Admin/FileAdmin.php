<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\File;
use App\Services\UserHelper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

final class FileAdmin extends AbstractAdmin
{
    private UserHelper $userHelper;
    public function __construct(UserHelper $userHelper)
    {
        parent::__construct();

        $this->userHelper = $userHelper;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('batch');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name')
        ;
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
                    return $this->userHelper->getAvatarWebPath($subject);
                },
                'template' => 'admin/avatar/image.html.twig',
            ])
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof File
            ? $object->getName()
            : 'File';
    }
}
