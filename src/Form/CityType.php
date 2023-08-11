<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\City;
use App\Form\DataMapper\CityDataMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CityType extends AbstractType
{
    private DataMapperInterface $cityDataMapper;

    public function __construct(CityDataMapper $cityDataMapper)
    {
        $this->cityDataMapper = $cityDataMapper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('idx', IntegerType::class)
        ;

        $builder->setDataMapper($this->cityDataMapper);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
