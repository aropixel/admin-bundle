<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 15:56
 */

namespace Aropixel\AdminBundle\EventListener;


use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Bundle\DoctrineBundle\Mapping\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\Mapping\ReflectionService;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


#[AsEntityListener(event: Events::loadClassMetadata, method: 'loadClassMetadata')]
class MappedSuperClassListener
{

    /** @var array */
    private $entitiesNames;
    private ?RuntimeReflectionService $reflectionService = null;

    /**
     * MapPageBundleSubscriber constructor.
     */
    public function __construct($entitiesNames)
    {
        $this->entitiesNames = $entitiesNames;
    }


    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        // Get the metadata of the entity to check
        $metadata = $args->getClassMetadata();

        /**
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
