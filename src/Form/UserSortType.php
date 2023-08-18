<?php

namespace App\Form;

use App\Entity\City;
use App\Form\DataMapper\UserSortDataMapper;
use App\Message\Query\GetUsersQuery;
use App\Repository\CityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sort', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'id' => GetUsersQuery::ORDER_BY_ID,
                    'Имя' => GetUsersQuery::ORDER_BY_NAME,
                    'Фамилия' => GetUsersQuery::ORDER_BY_SURNAME,
                ],
            ])
            ->add('order', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Возрастание' => GetUsersQuery::ORDER_ASC,
                    'Убывание' => GetUsersQuery::ORDER_DESC,
                ],
            ])
            ->add('sortByCity', EntityType::class, [
                'required' => false,
                'class' => City::class,
                'choice_label' => 'name',
                'query_builder' => function (CityRepository $cityRepository): QueryBuilder {
                    return $cityRepository->createQueryBuilder('c')
                        ->orderBy('c.idx', 'ASC');
                }
            ])
        ;

        $builder->setDataMapper(new UserSortDataMapper());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'data_class' => GetUsersQuery::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
