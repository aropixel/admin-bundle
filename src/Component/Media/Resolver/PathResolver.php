<?php

namespace Aropixel\AdminBundle\Component\Media\Resolver;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(PathResolverInterface::class)]
class PathResolver implements PathResolverInterface
{
    public function getImagePath(ImageInterface $image): string
    {
        return Image::UPLOAD_DIR . '/' . $image->getFilename();
    }

    public function getFilePath(FileInterface $file): string
    {
        return File::UPLOAD_DIR . '/' . $file->getFilename();
    }
}
