<?php

declare(strict_types=1);

namespace GraffishLabs\SymfonyNatsTransport\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use GraffishLabs\SymfonyNatsTransport\Transport\NatsTransport;
use GraffishLabs\SymfonyNatsTransport\Transport\NatsTransportFactory;

class GraffishLabsSymfonyNatsTransportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $container->register(NatsTransport::class)
            ->addTag('messenger.transport');
        
        $container->register(NatsTransportFactory::class)
            ->addTag('messenger.transport_factory');
        
    }
} 