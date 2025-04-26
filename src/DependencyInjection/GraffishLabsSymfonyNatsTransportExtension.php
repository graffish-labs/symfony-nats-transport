<?php

declare(strict_types=1);

namespace GraffishLabs\SymfonyNatsTransport\DependencyInjection;

use GraffishLabs\SymfonyNatsTransport\Client\NatsClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class GraffishLabsSymfonyNatsTransportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        // $loader->load('services.yaml');

        // $configuration = new Configuration();
        // $config = $this->processConfiguration($configuration, $configs);

        // if ($config['enabled']) {
        //     $container->register(NatsClient::class)
        //         ->setArguments([
        //             new Reference('client'),
        //             $config['client']['base_uri'],
        //             $config['client']['api_key']
        //         ]);
        // }
    }
} 