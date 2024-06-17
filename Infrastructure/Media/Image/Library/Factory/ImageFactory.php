<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Library\Factory;

use Aropixel\AdminBundle\Domain\Media\Image\Library\Factory\ImageFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

class ImageFactory implements ImageFactoryInterface
{
    public function __construct(
        private readonly ClassNameResolverInterface $classNameResolver
    ) {
    }

    public function create(): ItemLibraryInterface
    {
        $imageClassName = $this->classNameResolver->getImageClassName();

        return new $imageClassName();
    }
}
