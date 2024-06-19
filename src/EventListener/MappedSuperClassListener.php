<?php

namespace Aropixel\AdminBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;

class MappedSuperClassListener
{
    private ?RuntimeReflectionService $reflectionService = null;

    /**
     * MapPageBundleSubscriber constructor.
     *
     * @param mixed[] $entitiesNames
     */
    public function __construct(
        private $entitiesNames
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        // Get the metadata of the entity to check
        $metadata = $args->getClassMetadata();

        /*
         * Check if the reflection class is part of the customized entities
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
