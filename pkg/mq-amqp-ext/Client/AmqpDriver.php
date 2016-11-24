<?php
namespace  Formapro\AmqpExt\Client;

use Formapro\AmqpExt\AmqpContext;
use Formapro\AmqpExt\AmqpMessage;
use Formapro\AmqpExt\AmqpQueue;
use Formapro\AmqpExt\AmqpTopic;
use Formapro\Fms\Exception\InvalidDestinationException;
use Formapro\Fms\Message as TransportMessage;
use Formapro\Fms\Queue;
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

        $transportMessage = $this->createTransportMessage($message);

        if ($message->getDelay()) {
            $destination = $this->createDelayedTopic($queue);
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
    public function createTransportMessage(Message $message)
    {
        $headers = $message->getHeaders();
        $properties = $message->getProperties();

        $headers['content_type'] = $message->getContentType();

        if ($message->getExpire()) {
            $headers['expiration'] = (string) ($message->getExpire() * 1000);
        }

        if ($message->getDelay()) {
            $properties['x-delay'] = (string) ($message->getDelay() * 1000);
        }

        if ($priority = $message->getPriority()) {
            if (false == array_key_exists($priority, $this->priorityMap)) {
                throw new \InvalidArgumentException(sprintf(
                    'Given priority could not be converted to client\'s one. Got: %s',
                    $priority
                ));
            }

            $headers['priority'] = $this->priorityMap[$priority];
        }

        $headers['delivery_mode'] = AmqpMessage::DELIVERY_MODE_PERSISTENT;

        $transportMessage = $this->context->createMessage();
        $transportMessage->setBody($message->getBody());
        $transportMessage->setHeaders($headers);
        $transportMessage->setProperties($properties);
        $transportMessage->setMessageId($message->getMessageId());
        $transportMessage->setTimestamp($message->getTimestamp());

        return $transportMessage;
    }

    /**
     * @param AmqpMessage $message
     *
     * {@inheritdoc}
     */
    public function createClientMessage(TransportMessage $message)
    {
        $clientMessage = new Message();

        $clientMessage->setBody($message->getBody());
        $clientMessage->setHeaders($message->getHeaders());
        $clientMessage->setProperties($message->getProperties());

        $clientMessage->setContentType($message->getHeader('content_type'));

        if ($delay = $message->getProperty('x-delay')) {
            if (false == is_numeric($delay)) {
                throw new \LogicException(sprintf('x-delay header is not numeric. "%s"', $delay));
            }

            $clientMessage->setDelay((int) ((int) $delay) / 1000);
        }

        if ($expiration = $message->getHeader('expiration')) {
            if (false == is_numeric($expiration)) {
                throw new \LogicException(sprintf('expiration header is not numeric. "%s"', $expiration));
            }

            $clientMessage->setExpire((int) ((int) $expiration) / 1000);
        }

        if ($priority = $message->getHeader('priority')) {
            if (false === $clientPriority = array_search($priority, $this->priorityMap, true)) {
                throw new \LogicException(sprintf('Cant convert transport priority to client: "%s"', $priority));
            }

            $clientMessage->setPriority($priority);
        }

        $clientMessage->setMessageId($message->getMessageId());
        $clientMessage->setTimestamp($message->getTimestamp());

        return $clientMessage;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
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
