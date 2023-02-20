<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 15:56
 */

namespace Aropixel\AdminBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;



class MappedSuperClassSubscriber implements EventSubscriber
{

    /** @var array */
    private $entitiesNames;

    /**
     * MapPageBundleSubscriber constructor.
     */
    public function __construct($entitiesNames)
    {
        $this->entitiesNames = $entitiesNames;
    }


    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        // Get the metadata of the entity to check
        $metadata = $eventArgs->getClassMetadata();

        /**
         * Check if the reflection class is part of the customized entities
         */
        foreach ($this->entitiesNames as $interface => $model) {

            if ($metadata->getName() == $model) {

                if ($metadata->isMappedSuperclass) {

                    $metadata->isMappedSuperclass = false;

                }

            }

        }

    }


}
