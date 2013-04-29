<?php

namespace RNK\ElFinderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $rootNode = $treeBuilder->root('rnk_el_finder');

        $rootNode
            ->children()
                ->scalarNode('locale')->defaultValue('en_US.UTF8')->end()
            ->end()
        ;

        $rootNode
            ->children()
                ->scalarNode('locale')->defaultValue('en_US.UTF8')->end()
            ->end()
            ->children()
                ->arrayNode('connector')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('debug')->defaultValue(false)->end()
                        ->arrayNode('roots')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('driver')->defaultValue('LocalFileSystem')->end()
                                    ->scalarNode('path')->isRequired()->end()
                                    ->arrayNode('upload_allow')
                                        ->prototype('scalar')->end()
                                        ->defaultValue(array())
                                    ->end()
                                    ->arrayNode('upload_deny')
                                        ->prototype('scalar')->end()
                                        ->defaultValue(array())
                                    ->end()
                                    ->scalarNode('show_hidden_files')->defaultValue(false)->end()
                                    ->scalarNode('upload_max_size')->defaultValue('10M')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}