<?php

namespace Aropixel\AdminBundle\Domain\Media\Resolver;

use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;

interface PathResolverInterface
{
    public function getImagePath(ImageInterface $image): string;

    public function getFilePath(FileInterface $file): string;
}
