<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\City;
use App\Entity\User;
use App\Form\DataMapper\UserAdminDataMapper;
use App\Validator as AppAssert;
use App\Validator\User as Validator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\MediaBundle\Form\Type\MediaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class UserAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('batch');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var \App\Entity\User $subject */
        $subject = $this->getSubject();

        $form
            ->with('Имя и фамилия')
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Validator\Compound\UserNameCompound(),
                ],
            ])
            ->add('surName', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Validator\Compound\UserSurNameCompound(),
                ],
            ])
            ->end()
            ->with('Выберите город')
            ->add('city', ModelType::class, [
                'class' => City::class,
                'property' => 'name',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->end()
            ->with('Выберите изображение')
            ->add('media', MediaType::class, [
                'provider' => 'sonata.media.provider.image',
                'context' => 'user',
                'label' => false,
                'media' => $subject->getMedia(),
                'constraints' => [
                    new AppAssert\SonataMedia([
                        new Assert\File([
                            'maxSize' => '1024K',
                            'mimeTypes' => ['image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png'],
                            'extensions' => ['png', 'jpeg', 'jpg'],
                            'extensionsMessage' => "Недопустимое расширение файла {{ extension }}. Разрешенные расширения {{ extensions }}.",
                        ])
                    ])
                ],
            ])
            ->end();

        $form->getFormBuilder()->setDataMapper(new UserAdminDataMapper());
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('name', filterOptions: [
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => User::class,
                    'choice_label' => 'name',
                ],
                'label' => 'Имя',
            ])
            ->add('city', filterOptions: [
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => City::class,
                    'choice_label' => 'name',
                ],
                'label' => 'Город',
            ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name',)
            ->addIdentifier('surName')
            ->add('city.name')
            ->add('media', null, [
                'label' => 'Aватар',
                'template' => 'admin/user/list/media.html.twig',
            ])
            ->add(name: ListMapper::NAME_ACTIONS, fieldDescriptionOptions: [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Имя и фамилия')
                ->add('name')
                ->add('surName')
            ->end()
            ->with('Город пользователя')
                ->add('city.name')
            ->end()
            ->with('Изображение пользователя')
                ->add('media', null, [
                    'label' => 'Aватар',
                    'template' => 'admin/user/show/media.html.twig',
                ])
            ->end();
    }

    public function toString(object $object): string
    {
        return $object instanceof User
            ? $object->getName()
            : 'User';
    }
}
