<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Fixture;

use Aropixel\AdminBundle\Domain\Media\Image\Library\Factory\ImageFactoryInterface;
use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\Image;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\File;

class ImageFixture
{
    public function __construct(
        private readonly FilesystemOperator $privateStorage,
        private readonly ImageFactoryInterface $imageFactory,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function createImage(AttachedImageInterface $attachedImage, string $relativePath): void
    {
        // Copy the physical image
        $file = new File($relativePath);
        $this->privateStorage->write(
            Image::UPLOAD_DIR . '/' . $file->getFilename(),
            file_get_contents($relativePath)
        );

        /**
         * Create the image entity in the library
         * @var Image $image
         */
        $image = $this->imageFactory->create();
        $image->setFile($file);
        $image->setCategory(get_class($attachedImage));
        $image->setFilename(Image::UPLOAD_DIR . '/' . $file->getFilename());
        $image->setTitle($attachedImage->getTitle() ?? $file->getFilename());
        $image->setExtension($file->getFilename());
        $this->managerRegistry->getManagerForClass(get_class($image))->persist($image);

        // Attach the image to the attached image
        $attachedImage->setImage($image);
    }
}
