<?php

declare(strict_types=1);

namespace GraffishLabs\SymfonyNatsTransport\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('graffish_labs_symfony_nats_transport');
        return $treeBuilder;
    }
} 