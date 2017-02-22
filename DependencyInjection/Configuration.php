<?php

namespace Isometriks\Bundle\GoogleApiBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('isometriks_google_api');

        $rootNode
            ->children()
                ->arrayNode('client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('application_name')->end()
                        ->scalarNode('client_id')->end()
                        ->scalarNode('client_secret')->end()
                        ->variableNode('redirect_route')->defaultValue('isometriks_google_redirect')->end()
                        ->scalarNode('developer_key')->end()
                        ->scalarNode('include_granted_scopes')->defaultFalse()->end()
                        ->scalarNode('access_type')->defaultValue('online')->end()
                    ->end()
                ->end()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('storage')->defaultValue('isometriks_google_api.storage.session')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
