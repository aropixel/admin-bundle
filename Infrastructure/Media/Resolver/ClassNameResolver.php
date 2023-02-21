<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Resolver;

use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ClassNameResolver implements ClassNameResolverInterface
{

    private ParameterBagInterface $parameterBag;

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getImageClassName(): string
    {
        $entitiesClassNames = $this->parameterBag->get('aropixel_admin.entities');
        return $entitiesClassNames[ImageInterface::class];
    }

    public function getFileClassName(): string
    {
        $entitiesClassNames = $this->parameterBag->get('aropixel_admin.entities');
        return $entitiesClassNames[FileInterface::class];
    }
}