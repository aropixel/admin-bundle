<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Library\Factory;

use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

interface ImageFactoryInterface
{
    public function create(): ItemLibraryInterface;
}
