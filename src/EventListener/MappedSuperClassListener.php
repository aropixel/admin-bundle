<?php

namespace Aropixel\AdminBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;

/**
 * This listener intervenes at runtime when loading Doctrine class metadata.
 * Its role is to transform the bundle's MappedSuperclasses into actual Entities
 * if they have not been overridden by the user.
 *
 * This allows the bundle to work "out-of-the-box" without configuration,
 * while remaining extensible via inheritance.
 */
#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 8192)]
class MappedSuperClassListener
{
    /**
     * @param array<class-string,class-string> $entitiesNames List of concrete classes (often indexed by interface)
     */
    public function __construct(
        private readonly array $entitiesNames
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        // Get the metadata of the class being loaded
        $metadata = $args->getClassMetadata();

        /*
         * If the loaded class matches one of the configured concrete classes
         * and it is marked as a MappedSuperclass, we transform it into an Entity.
         * This will trigger the creation of the corresponding table in the database.
         */
        foreach ($this->entitiesNames as $interface => $model) {
            if ($metadata->getName() === $model) {
                if ($metadata->isMappedSuperclass) {
                    $metadata->isMappedSuperclass = false;
                }
            }
        }
    }
}
