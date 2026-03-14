<?php

namespace Aropixel\AdminBundle\EventListener\Doctrine;

use Aropixel\AdminBundle\Entity\AttachedFileInterface;
use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::onFlush)]
class CleanupAttachedItemsListener
{
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        // On parcourt les entités mises à jour
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            
            // Si c'est un fichier attaché dont le fichier est passé à null
            if ($entity instanceof AttachedFileInterface && null === $entity->getFile()) {
                $em->remove($entity);
                $uow->computeChangeSet($em->getClassMetadata(get_class($entity)), $entity);
            }
            
            // Si c'est une image attachée dont l'image est passée à null
            if ($entity instanceof AttachedImageInterface && null === $entity->getImage()) {
                $em->remove($entity);
                $uow->computeChangeSet($em->getClassMetadata(get_class($entity)), $entity);
            }
        }
    }
}
