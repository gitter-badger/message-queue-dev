<?php
namespace Formapro\MessageQueue\Client;

use Formapro\Fms\Exception\InvalidDestinationException;
use Formapro\Fms\Message as TransportMessage;
use Formapro\Fms\Queue;
use Formapro\MessageQueue\Transport\Null\NullContext;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullQueue;

class NullDriver implements DriverInterface
{
    /**
     * @var NullContext
     */
    protected $context;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param NullContext $session
     * @param Config      $config
     */
    public function __construct(NullContext $session, Config $config)
    {
        $this->context = $session;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     *
     * @return NullMessage
     */
    public function createTransportMessage(Message $message)
    {
        $headers = $message->getHeaders();
        $headers['content_type'] = $message->getContentType();
        $headers['expiration'] = $message->getExpire();
        $headers['delay'] = $message->getDelay();
        $headers['priority'] = $message->getPriority();

        $transportMessage = $this->context->createMessage();
        $transportMessage->setBody($message->getBody());
        $transportMessage->setHeaders($headers);
        $transportMessage->setProperties($message->getProperties());
        $transportMessage->setTimestamp($message->getTimestamp());
        $transportMessage->setMessageId($message->getMessageId());

        return $transportMessage;
    }

    /**
     * {@inheritdoc}
     *
     * @param NullMessage $message
     */
    public function createClientMessage(TransportMessage $message)
    {
        $clientMessage = new Message();
        $clientMessage->setBody($message->getBody());
        $clientMessage->setHeaders($message->getHeaders());
        $clientMessage->setProperties($message->getProperties());
        $clientMessage->setTimestamp($message->getTimestamp());
        $clientMessage->setMessageId($message->getMessageId());

        if ($contentType = $message->getHeader('content_type')) {
            $clientMessage->setContentType($contentType);
        }

        if ($expiration = $message->getHeader('expiration')) {
            $clientMessage->setExpire($expiration);
        }

        if ($delay = $message->getHeader('delay')) {
            $clientMessage->setDelay($delay);
        }

        if ($priority = $message->getHeader('priority')) {
            $clientMessage->setPriority($priority);
        }

        return $clientMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        return $this->context->createQueue($queueName);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Queue $queue, Message $message)
    {
        InvalidDestinationException::assertDestinationInstanceOf($queue, NullQueue::class);

        $transportMessage = $this->createTransportMessage($message);

        $this->context->createProducer()->send($queue, $transportMessage);
    }
}
