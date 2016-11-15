<?php
namespace Formapro\MessageQueue\Client;

use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\Queue;
use Formapro\MessageQueue\Transport\Null\NullContext;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullQueue;

class NullDriver implements DriverInterface
{
    /**
     * @var NullContext
     */
    protected $session;

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
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     *
     * @return NullMessage
     */
    public function createTransportMessage()
    {
        return $this->session->createMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        return $this->session->createQueue($queueName);
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

        $destination = $queue;

        $headers = $message->getHeaders();
        $headers['content_type'] = $message->getContentType();
        $headers['expiration'] = $message->getExpire();
        $headers['delay'] = $message->getDelay();
        $headers['priority'] = $message->getPriority();

        $transportMessage = $this->createTransportMessage();
        $transportMessage->setBody($message->getBody());
        $transportMessage->setProperties($message->getProperties());
        $transportMessage->setMessageId($message->getMessageId());
        $transportMessage->setTimestamp($message->getTimestamp());
        $transportMessage->setHeaders($headers);

        $this->session->createProducer()->send($destination, $transportMessage);
    }
}
