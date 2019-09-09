<?php

namespace Aropixel\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aropixel_admin');
        $rootNode
             ->children()

                ->arrayNode('filter_sets')
                     ->defaultValue(array())
                     ->useAttributeAsKey('name')
                     ->prototype('variable')->end()
                ->end()
                ->arrayNode('editor_filter_sets')
                     ->defaultValue(array())
                     ->useAttributeAsKey('name')
                     ->prototype('variable')->end()
                ->end()
                ->arrayNode('client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('Client')->end()
                        ->scalarNode('link')->defaultValue('')->end()
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
                        ->scalarNode('menu_position')->defaultValue('top')->end()
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
                                ->then(function($v) { return preg_split('/\s*,\s*/', $v); })
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