<?php

declare(strict_types=1);

namespace GraffishLabs\SymfonyNatsTransport\Transport;

use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Basis\Nats\Client;
use Basis\Nats\Configuration;
use Basis\Nats\Consumer\Consumer;
use Basis\Nats\Queue;
use Basis\Nats\Stream\Stream;
use Basis\Nats\Message\Ack;
use Basis\Nats\Message\Msg;
use Basis\Nats\Message\Nak;
use Symfony\Component\Uid\Uuid;
use Exception;
use LogicException;
use InvalidArgumentException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Basis\Nats\Stream\RetentionPolicy;
use Basis\Nats\Consumer\Configuration as ConsumerConfiguration;
use Symfony\Component\Messenger\Attribute\AsMessage;

class NatsTransport implements TransportInterface, MessageCountAwareInterface
{
    private const DEFAULT_OPTIONS = [
        'delay' => 0.001,
        'batching' => 10,
    ];

    protected Consumer $consumer;
    protected Queue $queue;
    protected Stream $stream;
    protected Client $client;
    protected string $topic;
    protected string $streamName;
    protected array $configuration;
    protected SerializerInterface $serializer;

    public function __construct(#[\SensitiveParameter] string $dsn, SerializerInterface $serializer, array $options = [])
    {
        $this->serializer = $serializer;
        $this->prepareFromDsn($dsn, $options);
    }

    public function send(Envelope $envelope): Envelope
    {
        $this->buildStream();
        $uuid = (string) Uuid::v4();
        $envelope = $envelope->with(new TransportMessageIdStamp($uuid));
        try {
            $encodedMessage = $this->serializer->encode($envelope);
            $this->stream->put($this->streamName.".".$this->topic, $encodedMessage);
        } catch (Exception $e) {
            $realError = $envelope->last(ErrorDetailsStamp::class);
            throw new Exception($realError->getExceptionMessage());
        }
        return $envelope;
    }

    public function get(): iterable
    {
        $this->buildConsumer();
        $messages = $this->queue->fetchAll($this->configuration['batching']);
        $envelopes = [];

        /** @var Msg */
        foreach ($messages as $message) {
            if (empty($message->payload->body)) {
                continue;
            }

            try {
                $decoded = $this->serializer->decode(json_decode($message->payload->body, true));
                $envelope = $decoded->with(new TransportMessageIdStamp($message->replyTo));
                $envelopes[] = $envelope;
            } catch (Exception $e) {
                $this->sendNak($message->replyTo);
                throw $e;
            }
        }

        return $envelopes;
    }

    protected function sendNak($id) {
        $this->client->connection->sendMessage(new Nak([
            'subject' => $id,
            'delay' => 0, //TODO
        ]));
    }

    public function ack(Envelope $envelope): void
    {
        $id = $this->findReceivedStamp($envelope)->getId();
        $this->client->connection->sendMessage(new Ack([
            'subject' => $id
        ]));
    }

    public function reject(Envelope $envelope): void
    {
        $id = $this->findReceivedStamp($envelope)->getId();
        $this->sendNak($id);
    }

    public function getMessageCount(): int
    {
        $info = json_decode($this->consumer->info()->body);
        return $info->num_pending;
    }

    private function findReceivedStamp(Envelope $envelope): TransportMessageIdStamp
    {
        /** @var TransportMessageIdStamp|null $receivedStamp */
        $receivedStamp = $envelope->last(TransportMessageIdStamp::class);

        if (null === $receivedStamp) {
            throw new LogicException('No ReceivedStamp found on the Envelope.');
        }

        return $receivedStamp;
    }

    protected function buildStream() {
        $stream = $this->client->getApi()->getStream($this->streamName);
        $stream->getConfiguration()->setRetentionPolicy(RetentionPolicy::WORK_QUEUE)
            ->setSubjects([$this->streamName.".*"]);
        $stream->createIfNotExists();
        $this->stream = $stream;
    }

    protected function buildConsumer()
    {
        $this->buildStream();
        $consumer = $this->stream->getConsumer($this->topic);
        $consumer->getConfiguration()->setSubjectFilter($this->streamName.".".$this->topic);
        $consumer->setBatching($this->configuration['batching']);
        if(!$consumer->exists()) {
            $consumer->create();
        }
        $this->consumer = $consumer;
        $this->queue = $consumer->getQueue();
        $this->queue->setTimeout($this->configuration['delay']);
    }

    protected function prepareFromDsn(#[\SensitiveParameter] string $dsn, array $options = []): void
    {
        if (false === $components = parse_url($dsn)) {
            throw new InvalidArgumentException('The given NATS DSN is invalid.');
        }
        $connectionCredentials = [
            'host' => $components['host'],
            'port' => $components['port'] ?? 4222,
        ];
        $path = $components['path'];

        if (empty($path) || strlen($path) < 4) {
            throw new InvalidArgumentException('NATS Stream name not provided.');
        }

        $query = [];
        if (isset($components['query'])) {
            parse_str($components['query'], $query);
        }
        $explodedDsn = explode('/', $dsn);

        $configuration = [];
        $configuration += $options + $query + self::DEFAULT_OPTIONS;

        $clientConnectionSettings = [
            'host' => $connectionCredentials['host'],
            'lang' => 'php',
            'pedantic' => false,
            'port' => $connectionCredentials['port'],
            'reconnect' => true,
        ];
        if (isset($components['user']) && isset($components['pass']) && !empty($components['user']) && !empty($components['pass'])) {
            $clientConnectionSettings['user'] = $components['user'];
            $clientConnectionSettings['pass'] = $components['pass'];
        }

        $this->streamName = end($explodedDsn);
        $this->topic = $options['transport_name'];

        $natsConfig = new Configuration($clientConnectionSettings);
        $natsConfig->setDelay(floatval($configuration['delay']));
        $this->client = new Client($natsConfig);
        $this->configuration = $configuration;
    }
}