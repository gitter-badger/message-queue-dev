<?php
namespace Formapro\MessageQueueStompTransport\Client;

use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;
use Formapro\MessageQueue\Transport\Exception\InvalidDestinationException;
use Formapro\MessageQueue\Transport\QueueInterface;
use Formapro\MessageQueueStompTransport\Transport\StompDestination;
use Formapro\MessageQueueStompTransport\Transport\StompSession;

class StompDriver implements DriverInterface
{
    /**
     * @var StompSession
     */
    private $session;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $priorityMap;

    /**
     * @param StompSession $session
     * @param Config $config
     */
    public function __construct(StompSession $session, Config $config)
    {
        $this->session = $session;
        $this->config = $config;

        $this->priorityMap = [
            MessagePriority::VERY_LOW => 0,
            MessagePriority::LOW => 1,
            MessagePriority::NORMAL => 2,
            MessagePriority::HIGH => 3,
            MessagePriority::VERY_HIGH => 4,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createTransportMessage()
    {
        return $this->session->createMessage();
    }

    /**
     * {@inheritdoc}
     *
     * @param StompDestination $queue
     */
    public function send(QueueInterface $queue, Message $message)
    {
        InvalidDestinationException::assertDestinationInstanceOf($queue, StompDestination::class);

        $destination = $queue;
        $headers = $message->getHeaders();

        $headers['content-type'] = $message->getContentType();

        if ($message->getExpire()) {
            $headers['expiration'] = (string) ($message->getExpire() * 1000);
        }

        if ($priority = $message->getPriority()) {
            if (false == array_key_exists($priority, $this->priorityMap)) {
                throw new \LogicException(sprintf('Cant convert client priority to transport: "%s"', $priority));
            }

            $headers['priority'] = $this->priorityMap[$priority];
        }

        if ($message->getDelay()) {
            $headers['x-delay'] = (string) ($message->getDelay() * 1000);

            $destination = $this->session->createTopic($queue->getTopicName().'.delayed');
            $destination->setType(StompDestination::TYPE_EXCHANGE);
            $destination->setDurable(true);
            $destination->setAutoDelete(false);
        }

        $transportMessage = $this->createTransportMessage();
        $transportMessage->setHeaders($headers);
        $transportMessage->setPersistent(true);
        $transportMessage->setBody($message->getBody());
        $transportMessage->setProperties($message->getProperties());

        if ($message->getMessageId()) {
            $transportMessage->setMessageId($message->getMessageId());
        }

        if ($message->getTimestamp()) {
            $transportMessage->setTimestamp($message->getTimestamp());
        }

        $this->session->createProducer()->send($destination, $transportMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        $queue = $this->session->createQueue($queueName);
        $queue->setDurable(true);
        $queue->setAutoDelete(false);
        $queue->setExclusive(false);

        $headers = $queue->getHeaders();
        $headers['x-max-priority'] = 4;
        $queue->setHeaders($headers);

        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }
}
