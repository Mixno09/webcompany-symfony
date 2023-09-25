<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SonataMediaMediaPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $adminServices = ['sonata.media.admin.media', 'sonata.media.admin.gallery', 'sonata.media.admin.gallery_item'];

        foreach ($adminServices as $service) {
            if ($container->hasDefinition($service)) {
                $container->removeDefinition($service);
            }
        }

        $definition = $container->findDefinition('sonata.admin.pool');

        $adminServiceCodes = $definition->getArgument(1);
        $adminServiceCodes = array_filter(
            $adminServiceCodes,
            static fn (string $code) => ! in_array($code, $adminServices, true),
        );

        $adminGroups = $definition->getArgument(2);
        $adminGroups = array_filter(
            $adminGroups,
            static fn (string $group) => ($group !== 'sonata_media'),
            ARRAY_FILTER_USE_KEY,
        );

        $adminClasses = $definition->getArgument(3);
        foreach ($adminClasses as $class => $services) {
            foreach ($services as $index => $service) {
                if (in_array($service, $adminServices, true)) {
                    unset($adminClasses[$class][$index]);
                }
            }
            if (count($adminClasses[$class]) === 0) {
                unset($adminClasses[$class]);
            }
        }

        $definition->replaceArgument(1, $adminServiceCodes);
        $definition->replaceArgument(2, $adminGroups);
        $definition->replaceArgument(3, $adminClasses);

        /** @var \Symfony\Component\DependencyInjection\Reference $reference */
        $reference = $definition->getArgument(0);
        $reference = $container->findDefinition((string) $reference);
        $factories = $reference->getArgument(0);
        $factories = array_filter(
            $factories,
            static fn (string $service) => ! in_array($service, $adminServices, true),
            ARRAY_FILTER_USE_KEY,
        );

        $reference->replaceArgument(0, $factories);
    }
}
