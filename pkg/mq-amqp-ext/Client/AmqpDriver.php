<?php
namespace  Formapro\AmqpExt\Client;

use Formapro\AmqpExt\AmqpContext;
use Formapro\AmqpExt\AmqpMessage;
use Formapro\AmqpExt\AmqpQueue;
use Formapro\AmqpExt\AmqpTopic;
use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\Queue;
use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;

class AmqpDriver implements DriverInterface
{
    /**
     * @var AmqpContext
     */
    protected $context;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $priorityMap;

    /**
     * @param AmqpContext $context
     * @param Config      $config
     */
    public function __construct(AmqpContext $context, Config $config)
    {
        $this->context = $context;
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
     *
     * @param AmqpQueue   $queue
     * @param AmqpMessage $queue
     */
    public function send(Queue $queue, Message $message)
    {
        InvalidDestinationException::assertDestinationInstanceOf($queue, AmqpQueue::class);

        $destination = $queue;

        $headers = $message->getHeaders();
        $properties = $message->getProperties();

        $headers['content_type'] = $message->getContentType();

        if ($message->getExpire()) {
            $headers['expiration'] = (string) ($message->getExpire() * 1000);
        }

        if ($message->getDelay()) {
            $properties['x-delay'] = (string) ($message->getDelay() * 1000);

            $destination = $this->createDelayedTopic($queue);
        }

        $headers['delivery_mode'] = AmqpMessage::DELIVERY_MODE_PERSISTENT;

        $transportMessage = $this->createTransportMessage();
        $transportMessage->setBody($message->getBody());
        $transportMessage->setHeaders($headers);
        $transportMessage->setProperties($properties);
        $transportMessage->setMessageId($message->getMessageId());
        $transportMessage->setTimestamp($message->getTimestamp());

        if ($message->getPriority()) {
            $this->setMessagePriority($transportMessage, $message->getPriority());
        }

        $this->context->createProducer()->send($destination, $transportMessage);
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpQueue
     */
    public function createQueue($queueName)
    {
        $queue = $this->context->createQueue($queueName);
        $queue->addFlag(AMQP_DURABLE);
        $queue->setArguments(['x-max-priority' => 4]);
        $this->context->declareQueue($queue);

        return $queue;
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpMessage
     */
    public function createTransportMessage()
    {
        return $this->context->createMessage();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param AmqpMessage $message
     * @param string      $priority
     */
    private function setMessagePriority(AmqpMessage $message, $priority)
    {
        if (false == array_key_exists($priority, $this->priorityMap)) {
            throw new \InvalidArgumentException(sprintf(
                'Given priority could not be converted to transport\'s one. Got: %s',
                $priority
            ));
        }

        $message->setHeader('priority', $this->priorityMap[$priority]);
    }

    /**
     * @param AmqpQueue $queue
     *
     * @return AmqpTopic
     */
    private function createDelayedTopic(AmqpQueue $queue)
    {
        $queueName = $queue->getQueueName();

        // in order to use delay feature make sure the rabbitmq_delayed_message_exchange plugin is installed.
        $delayTopic = $this->context->createTopic($queueName.'.delayed');
        $delayTopic->setType('x-delayed-message');
        $delayTopic->addFlag(AMQP_DURABLE);
        $delayTopic->setArguments([
            'x-delayed-type' => 'direct',
        ]);

        $this->context->declareTopic($delayTopic);
        $this->context->bind($delayTopic, $queue);

        return $delayTopic;
    }
}
