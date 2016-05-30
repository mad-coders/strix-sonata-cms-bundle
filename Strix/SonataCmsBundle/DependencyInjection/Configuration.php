<?php

namespace Strix\SonataCmsBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('strix_sonata_cms');

        $rootNode->children()
                ->scalarNode('language_entity')->defaultNull()->end()
                ->scalarNode('tree_entity')->defaultNull()->end()
                ->booleanNode('enable_router')->defaultFalse()->end()
                ->scalarNode('default_controller')->defaultValue('StrixSonataCmsBundle:Cms:index')->end()
            ->end();

        return $treeBuilder;
    }
}
