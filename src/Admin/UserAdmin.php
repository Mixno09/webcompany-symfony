<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\City;
use App\Entity\User;
use App\Services\UserHelper;
use phpDocumentor\Reflection\Types\This;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

final class UserAdmin extends AbstractAdmin implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('batch');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $user = $this->getSubject();
        $webPath = $this->getUserHelper()->getAvatarWebPath($user->getAvatar());

        $form
            ->with('Имя и фамилия')
                ->add('name', TextType::class)
                ->add('surName', TextType::class)
            ->end()
            ->with('Выберите город')
                ->add('city', ModelType::class, [
                    'class' => City::class,
                    'property' => 'name',
            ])
            ->end()
            ->with('Выберите изображение')
                ->add('avatar', FileType::class, [
                    'required' => false,
                    'mapped' => false,
                    'help' => "<img src={$webPath} class=admin-preview alt='' style='max-height: 200px; max-width: 200px;'/>",
                    'help_html' => true,
            ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('name')
            ->add('city', filterOptions: [
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => City::class,
                    'choice_label' => 'name',
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        dump($this->container);
        $list
            ->addIdentifier('name', )
            ->addIdentifier('surName')
            ->add('city.name')
            ->add('avatar.name')
            ->add('image', FieldDescriptionInterface::TYPE_STRING, fieldDescriptionOptions: [
                'accessor' => function ($subject) {
                    return $this->getUserHelper()->getAvatarWebPath($subject->getAvatar());
                },
                'template' => 'admin/user/image.html.twig',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Пользователь')
                ->with('Имя и фамилия')
                    ->add('name')
                    ->add('surName')
                ->end()
            ->end()
            ->tab('Город')
                ->with('Город пользователя')
                    ->add('city.name')
                ->end()
            ->end()
            ->tab('Изображение')
                ->with('Изображение пользователя')
                ->add('avatar.name')
                ->add('image', FieldDescriptionInterface::TYPE_STRING, [
                    'accessor' => function ($subject) {
                        return $this->getUserHelper()->getAvatarWebPath($subject->getAvatar());
                    },
                    'template' => 'admin/user/image.html.twig',
                ])
                ->end()
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
        return $object instanceof User
            ? $object->getName()
            : 'User';
    }
}
