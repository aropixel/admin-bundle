<?php

namespace Aropixel\AdminBundle\Component\Media\Image\Fixture;

use Aropixel\AdminBundle\Component\Media\Image\Library\Factory\ImageFactoryInterface;
use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\Image;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\File;

class ImageFixture
{
    public function __construct(
        private readonly ImageFactoryInterface $imageFactory,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function createImage(AttachedImage $attachedImage, string $relativePath): void
    {
        $file = new File($relativePath);

        /**
         * Create the image entity in the library.
         *
         * @var Image $image
         */
        $image = $this->imageFactory->create();
        $image->setFile($file);
        $image->setCategory($attachedImage::class);
        $image->setFilename(Image::UPLOAD_DIR . '/' . $file->getFilename());
        $image->setTitle($attachedImage->getTitle() ?? $file->getFilename());
        $image->setExtension($file->getFilename());
        $this->managerRegistry->getManagerForClass($image::class)->persist($image);

        // Attach the image to the attached image
        $attachedImage->setImage($image);
    }
}
