<?php

namespace Aropixel\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Aropixel\AdminBundle\DependencyInjection\Compiler\MenuCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AropixelAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MenuCompilerPass());
    }
}
