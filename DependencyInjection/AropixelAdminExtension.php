<?php

namespace Aropixel\AdminBundle\DependencyInjection;

use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AropixelAdminExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerParameters($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('form.yml');
    }

    public function registerParameters(ContainerBuilder $container, array $config)
    {
        // $config['filter_sets']['admin_thumbnail']['label'] = "Vignette d'administration";
        foreach ($config['filter_sets'] as $className => $filters) {
            $config['filter_sets'][$className]['admin_thumbnail'] = "Vignette d'administration";
        }

        $container->setParameter('aropixel_admin.filter_sets', $config['filter_sets']);
        $container->setParameter('aropixel_admin.editor_filter_sets', $config['editor_filter_sets']);
        $container->setParameter('aropixel_admin.copyright', $config['copyright']);
        $container->setParameter('aropixel_admin.client', $config['client']);
        $container->setParameter('aropixel_admin.theme', $config['theme']);
        $container->setParameter('aropixel_admin.forms', $config['forms']);
        $container->setParameter('aropixel_admin.entities', $config['entities']);
        $container->setParameter('aropixel_admin.entity.image', $config['entities'][ImageInterface::class]);
        $container->setParameter('aropixel_admin.entity.file', $config['entities'][FileInterface::class]);

        if (isset($config['form_translations'])) {
            //            $container->setParameter('aropixel_admin.form_translations.locales', $config['form_translations']['locales']);
            //            $container->setParameter('aropixel_admin.form_translations.default_locale', $config['form_translations']['default_locale'] ?: $container->getParameter('kernel.default_locale', 'fr'));
        } else {
            $container->setParameter('aropixel_admin.form_translations.locales', []);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        // get all bundles
        $bundles = $container->getParameter('kernel.bundles');
        $config = ['admin_thumbnail' => ['quality' => 75, 'filters' => ['upscale_thumbnail' => ['height' => 400, 'width' => 400]]], 'fallback_pixel' => ['quality' => 75, 'filters' => ['strip' => []]], 'admin_preview' => ['quality' => 75, 'filters' => ['relative_resize' => ['widen' => 800]]], 'admin_crop' => ['quality' => 75, 'filters' => ['relative_resize' => ['widen' => 600]]]];

        $sizes = [100, 200, 300, 400, 600, 800];
        foreach ($sizes as $width) {
            $config['editor_' . $width] = ['quality' => 75, 'filters' => ['relative_resize' => ['widen' => $width]]];
        }

        $config['editor_100pc'] = ['quality' => 75, 'filters' => ['relative_resize' => ['widen' => 1200]]];

        $config['auto'] = ['quality' => 75, 'filters' => ['relative_resize' => ['widen' => 100]]];

        $liipConfig = [
            //            'loaders' => [
            //                'default' => [
            //                    'filesystem' => [
            //                        'data_root' => '%kernel.project_dir%/private'
            //                    ]
            //                ]
            //            ],
            //            'resolvers' => [
            //                'default' => [
            //                    'web_path' => [
            //                        'web_root' => '%kernel.project_dir%/public',
            //                        'cache_prefix' => 'media/cache'
            //                    ]
            //                ]
            //            ],
            'filter_sets' => $config,
        ];

        $container->prependExtensionConfig('liip_imagine', $liipConfig);

        if (isset($bundles['StofDoctrineExtensionsBundle'])) {
            // prepend the acme_something settings with the entity_manager_name
            $config = [
                'orm' => [
                    'default' => [
                        'timestampable' => true,
                        'sluggable' => true,
                        'sortable' => true,
                        'tree' => true,
                    ],
                ],
            ];
            $container->prependExtensionConfig('stof_doctrine_extensions', $config);
        }

        // process the configuration of AcmeHelloExtension
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $twigConfig = [];
        $twigConfig['globals']['admin_client'] = $config['client'];
        $twigConfig['globals']['admin_copyright'] = $config['copyright'];
        $twigConfig['globals']['admin_theme'] = $config['theme'];
        $container->prependExtensionConfig('twig', $twigConfig);
    }
}
