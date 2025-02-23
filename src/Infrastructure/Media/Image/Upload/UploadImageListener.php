<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Upload;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Aropixel\AdminBundle\Infrastructure\Media\PreUploadHandler;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;

class UploadImageListener
{
    public function __construct(
        private readonly FilesystemOperator $privateStorage,
        private readonly PreUploadHandler $preUploadHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function prePersist(ImageInterface $image): void
    {
        $this->preUploadHandler->handlePreUpload($image);

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
            $this->privateStorage->write(Image::UPLOAD_DIR.'/'.$image->getFilename(), file_get_contents($image->getFile()->getPathname()));
            unlink($image->getFile()->getPathname());
        }
        catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }

    public function postRemove(ImageInterface $image): void
    {
        try {
            $this->privateStorage->delete(Image::UPLOAD_DIR.'/'.$image->getFilename());
        }
        catch (\Throwable) {}
    }

}
