<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Form\DataTransformer\MediaTransformer;
use Sonata\MediaBundle\Form\Type\MediaType;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MediaTypeExtension extends AbstractTypeExtension
{
    private readonly MediaManagerInterface $mediaManager;

    public function __construct(
        #[Autowire(service: 'sonata.media.manager.media')] MediaManagerInterface $mediaManager
    ) {
        $this->mediaManager = $mediaManager;
    }

    public static function getExtendedTypes(): iterable
    {
        return [MediaType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new MediaTransformer($this->mediaManager, $options));

        if ($options['btn_delete'] === false || $options['media'] === null) {
            $builder->remove('unlink');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'error_bubbling' => false,
            'btn_delete' => true,
            'media' => null,
        ]);

        $resolver->setAllowedTypes('btn_delete', 'bool');
        $resolver->setAllowedTypes('media', ['null', MediaInterface::class]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['media'] = $options['media'];
    }
}
