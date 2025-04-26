<?php

declare(strict_types=1);

namespace GraffishLabs\SymfonyNatsTransport;

use GraffishLabs\SymfonyNatsTransport\DependencyInjection\GraffishLabsSymfonyNatsTransportExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GraffishLabsSymfonyNatsTransportBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new GraffishLabsSymfonyNatsTransportExtension();
    }
} 