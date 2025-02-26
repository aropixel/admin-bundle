<?php

namespace Aropixel\AdminBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;

class MappedSuperClassListener
{
    /**
     * @param array<class-string,class-string> $entitiesNames
     */
    public function __construct(
        private readonly array $entitiesNames
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
