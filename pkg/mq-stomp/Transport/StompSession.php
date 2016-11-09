<?php
namespace Formapro\MessageQueueStompTransport\Transport;

use Formapro\MessageQueue\Transport\DestinationInterface;
use Formapro\MessageQueue\Transport\Exception\InvalidDestinationException;
use Formapro\MessageQueue\Transport\SessionInterface;

class StompSession implements SessionInterface
{
    /**
     * @var BufferedStompClient
     */
    private $stomp;

    public function __construct(BufferedStompClient $stomp)
    {
        $this->stomp = $stomp;
    }

    /**
     * {@inheritdoc}
     *
     * @return StompMessage
     */
    public function createMessage($body = null, array $properties = [], array $headers = [])
    {
        return new StompMessage($body, $properties, $headers);
    }

    /**
     * {@inheritdoc}
     *
     * @return StompDestination
     */
    public function createQueue($name)
    {
        return new StompDestination($name);
    }

    /**
     * {@inheritdoc}
     *
     * @return StompDestination
     */
    public function createTopic($name)
    {
        $topic = new StompDestination($name);
        $topic->setType(StompDestination::TYPE_EXCHANGE);

        return $topic;
    }

    /**
     * {@inheritdoc}
     *
     * @param StompDestination $destination
     *
     * @return StompMessageConsumer
     */
    public function createConsumer(DestinationInterface $destination)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, StompDestination::class);

        return new StompMessageConsumer($this->stomp, $destination);
    }

    /**
     * {@inheritdoc}
     *
     * @return StompProducer
     */
    public function createProducer()
    {
        return new StompProducer($this->stomp);
    }

    /**
     * {@inheritdoc}
     */
    public function declareTopic(DestinationInterface $destination)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function declareQueue(DestinationInterface $destination)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function declareBind(DestinationInterface $source, DestinationInterface $target)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->stomp->disconnect();
    }
}
