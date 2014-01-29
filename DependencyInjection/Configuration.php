<?php
namespace Kmfk\Bundle\HateoasBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('hateoas');

        $rootNode
            ->children()
                ->scalarNode('host')
                    ->defaultValue(null)
                    ->info('The API host')
                    ->example('http://api.example.com/')
                ->end()
                ->scalarNode('prefix')
                    ->defaultValue(null)
                    ->info('An optional Path Prefix for the API Url')
                    ->example('/api/')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
