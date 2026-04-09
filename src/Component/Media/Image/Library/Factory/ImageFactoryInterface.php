<?php

namespace Aropixel\AdminBundle\Component\Media\Image\Library\Factory;

use Aropixel\AdminBundle\Entity\ImageInterface;

interface ImageFactoryInterface
{
    public function create(): ImageInterface;
}
