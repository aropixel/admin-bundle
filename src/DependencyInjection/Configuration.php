<?php

namespace Aropixel\AdminBundle\DependencyInjection;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserImage;
use Aropixel\AdminBundle\Entity\UserImageInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('aropixel_admin');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->arrayNode('filter_sets')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->prototype('variable')->end()
            ->end()
            ->arrayNode('editor_filter_sets')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->prototype('variable')->end()
            ->end()
            ->arrayNode('entities')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode(ImageInterface::class)->defaultValue(Image::class)->end()
            ->scalarNode(FileInterface::class)->defaultValue(File::class)->end()
            ->scalarNode(UserInterface::class)->defaultValue(User::class)->end()
            ->scalarNode(UserImageInterface::class)->defaultValue(UserImage::class)->end()
            ->end()
            ->end()
            ->arrayNode('forms')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode(UserInterface::class)->defaultValue(UserType::class)->end()
            ->end()
            ->end()
            ->arrayNode('client')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('name')->defaultValue('Client')->end()
            ->scalarNode('link')->defaultValue('')->end()
            ->scalarNode('email')->defaultValue('')->end()
            ->end()
            ->end()
            ->arrayNode('copyright')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('name')->defaultValue('Aropixel')->end()
            ->scalarNode('link')->defaultValue('http://www.aropixel.com')->end()
            ->end()
            ->end()
            ->arrayNode('theme')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('login_img')->defaultValue('')->end()
            ->scalarNode('menu_position')->defaultValue('fullscreen')->end()
            ->scalarNode('logo_path')->defaultValue('bundles/aropixeladmin/img/logo.png')->end()
            ->scalarNode('logo_width')->defaultValue('150px')->end()
            ->scalarNode('logo_menu_path')->defaultValue('bundles/aropixeladmin/img/logo-opened-menu.gif')->end()
            ->scalarNode('logo_menu_width')->defaultValue('50px')->end()
            ->arrayNode('classes')
            ->children()
            ->scalarNode('card')->defaultValue('card')->end()
            ->scalarNode('card-header')->defaultValue('card-header')->end()
            ->scalarNode('card-title')->defaultValue('card-title')->end()
            ->scalarNode('card-body')->defaultValue('card-body')->end()
            ->scalarNode('card-footer')->defaultValue('card-footer')->end()
            ->scalarNode('datatable')->defaultValue('datatable')->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('form_translations')
            ->canBeEnabled()
            ->children()
            ->scalarNode('default_locale')->defaultNull()->end()
            ->arrayNode('locales')
            ->beforeNormalization()
            ->ifString()
            ->then(fn ($v) => preg_split('/\s*,\s*/', (string) $v))
            ->end()
            ->requiresAtLeastOneElement()
            ->prototype('scalar')->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
