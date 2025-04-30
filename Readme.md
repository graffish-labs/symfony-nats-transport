# Symfony NATS Transport

A Symfony Messenger transport implementation for NATS (Neural Autonomic Transport System). This package allows you to use NATS as a message broker for your Symfony Messenger component.

## Requirements

- PHP 8.1 or higher
- Symfony Framework Bundle 6.0 or higher
- Symfony Messenger 7.2 or higher
- NATS server

## Installation

```bash
composer require graffish-labs/symfony-nats-transport
```

## Features

- Automatic reconnection to NATS server
- Configurable connection options
- Support for Symfony Messenger's retry and failure handling
- Serialization of messages using Symfony's Serializer component

## License

This package is open-source software licensed under the MIT license. See the LICENSE file for details.
