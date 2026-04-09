<?php

namespace Aropixel\AdminBundle\DependencyInjection\Compiler;

use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * This CompilerPass configures Doctrine's ResolveTargetEntityListener.
 * It maps the bundle's interfaces (e.g., UserInterface) to the concrete classes
 * defined in the configuration (either the bundle's default classes or user-defined ones).
 *
 * Unlike the MappedSuperClassListener which acts at runtime, this pass runs during
 * container compilation to establish static relationships between entities.
 */
class DoctrineTargetEntitiesResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            // Get the Doctrine listener definition that handles interface replacement
            $resolveTargetEntityListener = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');
        } catch (InvalidArgumentException) {
            return;
        }

        // Get the list of configured entities (interface => concrete class)
        $entities = $container->getParameter('aropixel_admin.entities');
        foreach ($entities as $interface => $model) {
            $resolveTargetEntityListener->addMethodCall('addResolveTargetEntity', [$interface, $model, []]);
        }

        // Ensure the listener is correctly registered with Doctrine
        if (!$resolveTargetEntityListener->hasTag('doctrine.event_listener')) {
            $resolveTargetEntityListener
                ->addTag('doctrine.event_listener', ['event' => Events::loadClassMetadata])
                ->addTag('doctrine.event_listener', ['event' => Events::onClassMetadataNotFound])
            ;
        }
    }
}
