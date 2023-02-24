<?php


namespace Aropixel\AdminBundle\Infrastructure\Media\File\Library\Factory;

use Aropixel\AdminBundle\Domain\Media\File\Library\Factory\FileFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\ItemLibraryInterface;


class FileFactory implements FileFactoryInterface
{
    private ClassNameResolverInterface $classNameResolver;


    /**
     * @param ClassNameResolverInterface $classNameResolver
     */
    public function __construct(ClassNameResolverInterface $classNameResolver)
    {
        $this->classNameResolver = $classNameResolver;
    }

    public function create(): ItemLibraryInterface
    {
        $fileClassName = $this->classNameResolver->getFileClassName();
        return new $fileClassName();
    }

}
