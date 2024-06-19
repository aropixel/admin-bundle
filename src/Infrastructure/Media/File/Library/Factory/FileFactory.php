<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\File\Library\Factory;

use Aropixel\AdminBundle\Domain\Media\File\Library\Factory\FileFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

class FileFactory implements FileFactoryInterface
{
    public function __construct(
        private readonly ClassNameResolverInterface $classNameResolver
    ) {
    }

    public function create(): ItemLibraryInterface
    {
        $fileClassName = $this->classNameResolver->getFileClassName();

        return new $fileClassName();
    }
}
