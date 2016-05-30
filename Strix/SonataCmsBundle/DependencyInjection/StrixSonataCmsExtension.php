<?php

namespace Strix\SonataCmsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class StrixSonataCmsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('strix_sonata_cms.language_entity', $config['language_entity']);
        $container->setParameter('strix_sonata_cms.tree_entity', $config['tree_entity']);
        $container->setParameter('strix_sonata_cms.enable_router', $config['enable_router']);
        $container->setParameter('strix_sonata_cms.default_controller', $config['default_controller']);
    }
}
