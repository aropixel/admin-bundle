<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 21/02/2023 à 14:04
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Library\Factory;

use Aropixel\AdminBundle\Domain\Media\Image\Library\Factory\ImageFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\ItemLibraryInterface;


class ImageFactory implements ImageFactoryInterface
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
        $imageClassName = $this->classNameResolver->getImageClassName();
        return new $imageClassName();
    }

}
