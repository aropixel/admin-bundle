<?php

namespace Aropixel\AdminBundle\EventListener\Doctrine;

use Aropixel\AdminBundle\Entity\AttachedFileInterface;
use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::onFlush)]
class CleanupAttachedItemsListener
{
    private array $attachedClasses = [];

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof AttachedFileInterface && null === $entity->getFile()) {
                $em->remove($entity);
                $uow->computeChangeSet($em->getClassMetadata(get_class($entity)), $entity);
            }

            if ($entity instanceof AttachedImageInterface && null === $entity->getImage()) {
                $em->remove($entity);
                $uow->computeChangeSet($em->getClassMetadata(get_class($entity)), $entity);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof ImageInterface) {
                $this->removeAttachmentsTo($em, $entity, AttachedImageInterface::class, 'image');
            } elseif ($entity instanceof FileInterface) {
                $this->removeAttachmentsTo($em, $entity, AttachedFileInterface::class, 'file');
            }
        }
    }

    private function removeAttachmentsTo(EntityManagerInterface $em, object $item, string $attachedInterface, string $field): void
    {
        foreach ($this->getAttachedClasses($em, $attachedInterface) as $attachedClass) {
            foreach ($em->getRepository($attachedClass)->findBy([$field => $item]) as $attached) {
                $em->remove($attached);
            }
        }
    }

    private function getAttachedClasses(EntityManagerInterface $em, string $attachedInterface): array
    {
        if (isset($this->attachedClasses[$attachedInterface])) {
            return $this->attachedClasses[$attachedInterface];
        }

        $classes = [];

        foreach ($em->getMetadataFactory()->getAllMetadata() as $metadata) {
            if ($metadata->isMappedSuperclass || $metadata->getReflectionClass()->isAbstract()) {
                continue;
            }

            if (is_a($metadata->getName(), $attachedInterface, true)) {
                $classes[] = $metadata->getName();
            }
        }

        return $this->attachedClasses[$attachedInterface] = $classes;
    }
}
