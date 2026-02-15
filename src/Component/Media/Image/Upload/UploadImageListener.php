<?php

namespace Aropixel\AdminBundle\Component\Media\Image\Upload;

use Aropixel\AdminBundle\Component\Media\PreUploadHandler;
use Aropixel\AdminBundle\Component\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsEntityListener(event: Events::prePersist, entity: '%aropixel_admin.entity.image%')]
#[AsEntityListener(event: Events::postPersist, entity: '%aropixel_admin.entity.image%')]
#[AsEntityListener(event: Events::postRemove, entity: '%aropixel_admin.entity.image%')]
class UploadImageListener
{
    public function __construct(
        private readonly FilesystemOperator $privateStorage,
        private readonly PreUploadHandler $preUploadHandler,
        private readonly LoggerInterface $logger,
        private readonly PathResolverInterface $pathResolver,
    ) {
    }

    public function prePersist(ImageInterface $image): void
    {
        $this->preUploadHandler->handlePreUpload($image);

        if (null === $image->getFile()) {
            return;
        }

        [$width, $height] = getimagesize($image->getFile()->getPathname());
        $image->setWidth($width);
        $image->setHeight($height);
    }

    public function postPersist(ImageInterface $image): void
    {
        if (null === $image->getFile()) {
            return;
        }

        try {
            $this->privateStorage->write(
                $this->pathResolver->getImagePath($image),
                file_get_contents($image->getFile()->getPathname())
            );

            if ($image->getFile() instanceof UploadedFile) {
                unlink($image->getFile()->getPathname());
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }

    public function postRemove(ImageInterface $image): void
    {
        try {
            $this->privateStorage->delete($this->pathResolver->getImagePath($image));
        } catch (\Throwable) {
        }
    }
}
