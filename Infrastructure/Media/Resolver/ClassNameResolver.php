<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Resolver;

use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\FileInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ClassNameResolver implements ClassNameResolverInterface
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public function getImageClassName(): string
    {
        $entitiesClassNames = $this->parameterBag->get('aropixel_admin.entities');

        return $entitiesClassNames[AttachedImageInterface::class];
    }

    public function getFileClassName(): string
    {
        $entitiesClassNames = $this->parameterBag->get('aropixel_admin.entities');

        return $entitiesClassNames[FileInterface::class];
    }
}
