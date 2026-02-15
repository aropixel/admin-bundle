<?php

namespace Aropixel\AdminBundle\Component\Media\Image\Library\Factory;

use Aropixel\AdminBundle\Component\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;

class ImageFactory implements ImageFactoryInterface
{
    public function __construct(
        private readonly ClassNameResolverInterface $classNameResolver
    ) {
    }

    public function create(): ImageInterface
    {
        $imageClassName = $this->classNameResolver->getImageClassName();

        return new $imageClassName();
    }
}
