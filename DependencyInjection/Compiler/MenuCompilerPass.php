<?php
namespace Aropixel\AdminBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class MenuCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if(!$container->hasDefinition('aropixel_admin.main_menu'))
        {
            return;
        }
        $manager = $container->getDefinition('aropixel_admin.main_menu');
        foreach($container->findTaggedServiceIds('knp_menu.menu') as $id=>$service)
        {
//            $manager->addMethodCall('addItem',array($id,new Reference($id)));
            $container->setAlias('knp_menu.menu.'.$id,$id);
        }
    }
}
