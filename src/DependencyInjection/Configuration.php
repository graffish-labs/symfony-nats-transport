<?php

declare(strict_types=1);

namespace GraffishLabs\SymfonyNatsTransport\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nats');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->arrayNode('client')
                    ->children()
                        ->scalarNode('base_uri')->isRequired()->end()
                        ->scalarNode('api_key')->isRequired()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
} 