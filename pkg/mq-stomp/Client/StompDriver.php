<?php
namespace Formapro\Stomp\Client;

use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\Message as JMSMessage;
use Formapro\Jms\Queue;
use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;
use Formapro\Stomp\StompContext;
use Formapro\Stomp\StompDestination;
use Formapro\Stomp\StompMessage;

class StompDriver implements DriverInterface
{
    /**
     * @var StompContext
     */
    private $context;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $priorityMap;

    /**
     * @param StompContext $context
     * @param Config       $config
     */
    public function __construct(StompContext $context, Config $config)
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
     */
    public function createTransportMessage()
    {
        return $this->context->createMessage();
    }

    /**
     * {@inheritdoc}
     *
     * @param StompDestination $queue
     */
    public function send(Queue $queue, Message $message)
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

            $destination = $this->context->createTopic($queue->getStompName().'.delayed');
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

        $this->context->createProducer()->send($destination, $transportMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        $queue = $this->context->createQueue($queueName);
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

    /**
     * @param StompMessage $message
     *
     * {@inheritdoc}
     */
    public function convertTransportToClientMessage(JMSMessage $message)
    {
        $clientMessage = new Message();
        $clientMessage->setBody($message->getBody());
        $clientMessage->setHeaders($message->getHeaders());
        $clientMessage->setProperties($message->getProperties());

        $clientMessage->setContentType($message->getHeader('content-type'));

        if ($delay = $message->getHeader('x-delay')) {
            if (false == is_numeric($delay)) {
                throw new \LogicException('x-delay header is not numeric. "%s"', $delay);
            }

            $clientMessage->setDelay((int) ((int) $delay) / 1000);
        }

        if ($expiration = $message->getHeader('expiration')) {
            if (false == is_numeric($expiration)) {
                throw new \LogicException('expiration header is not numeric. "%s"', $expiration);
            }

            $clientMessage->setExpire((int) ((int) $expiration) / 1000);
        }

        $clientMessage->setMessageId($message->getMessageId());
        $clientMessage->setPriority($message->getHeader('priority'));
        $clientMessage->setTimestamp($message->getTimestamp());

        return $clientMessage;
    }
}
