<?php

declare(strict_types=1);

namespace GraffishLabs\SymfonyNatsTransport\Transport;

use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Serializer\SerializerInterface;

class NatsTransportFactory implements TransportFactoryInterface
{
    public function createTransport(#[\SensitiveParameter] string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new NatsTransport($dsn, $options);
    }

    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'nats://');
    }
} 