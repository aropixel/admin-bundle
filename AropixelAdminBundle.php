<?php

namespace Aropixel\AdminBundle;

use Aropixel\AdminBundle\DependencyInjection\Compiler\DoctrineTargetEntitiesResolverPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AropixelAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrineTargetEntitiesResolverPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }
}
