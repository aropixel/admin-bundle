<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 15:56
 */

namespace Aropixel\AdminBundle\EventListener;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Webmozart\Assert\Assert;


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
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->getName() === Image::class) {

            $this->convertToEntityIfNeeded($metadata);

            if (!$metadata->isMappedSuperclass) {
                $this->setAssociationMappings($metadata, $eventArgs->getEntityManager()->getConfiguration());
            } else {
                $this->unsetAssociationMappings($metadata);
            }

        }
    }

    private function convertToEntityIfNeeded(ClassMetadataInfo $metadata): void
    {
        if (false === $metadata->isMappedSuperclass) {
            return;
        }

        if ($this->entitiesNames['image'] === Image::class) {
            $metadata->isMappedSuperclass = false;
        }
    }

    private function setAssociationMappings(ClassMetadataInfo $metadata, Configuration $configuration): void
    {
        $class = $metadata->getName();
        if (!class_exists($class)) {
            return;
        }

        $metadataDriver = $configuration->getMetadataDriverImpl();
        Assert::isInstanceOf($metadataDriver, MappingDriver::class);

        foreach (class_parents($class) as $parent) {
            if (false === in_array($parent, $metadataDriver->getAllClassNames(), true)) {
                continue;
            }

            $parentMetadata = new ClassMetadata(
                $parent,
                $configuration->getNamingStrategy()
            );

            // Wakeup Reflection
            $parentMetadata->wakeupReflection($this->getReflectionService());

            // Load Metadata
            $metadataDriver->loadMetadataForClass($parent, $parentMetadata);

            if (false === $this->isResource($parentMetadata)) {
                continue;
            }

            if ($parentMetadata->isMappedSuperclass) {
                foreach ($parentMetadata->getAssociationMappings() as $key => $value) {
                    if ($this->isRelation($value['type']) && !isset($metadata->associationMappings[$key])) {
                        $metadata->associationMappings[$key] = $value;
                    }
                }
            }
        }
    }

    private function unsetAssociationMappings(ClassMetadataInfo $metadata): void
    {

        foreach ($metadata->getAssociationMappings() as $key => $value) {
            if ($this->isRelation($value['type'])) {
                unset($metadata->associationMappings[$key]);
            }
        }
    }

    private function isRelation(int $type): bool
    {
        return in_array(
            $type,
            [
                ClassMetadataInfo::MANY_TO_MANY,
                ClassMetadataInfo::ONE_TO_MANY,
                ClassMetadataInfo::ONE_TO_ONE,
            ],
            true
        );
    }


}
