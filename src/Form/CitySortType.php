<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitySortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sort', ChoiceType::class, [
                'choices' => [
                    'id' => 'id',
                    'Название Города' => 'name',
                    'Индекс Сортировки' => 'idx',
                ],
                'data' => 'idx',
            ])
            ->add('order', ChoiceType::class, [
                'choices' => [
                    'Возрастание' => 'ASC',
                    'Убывание' => 'DESC',
                ],
                'data' => 'ASC',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
