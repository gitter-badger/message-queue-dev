<?php
namespace Formapro\Stomp\Transport;

use Formapro\Jms\Destination;
use Formapro\Jms\JMSContext;
use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\MessageQueue\Util\UUID;

class StompContext implements JMSContext
{
    /**
     * @var BufferedStompClient
     */
    private $stomp;

    /**
     * @param BufferedStompClient $stomp
     */
    public function __construct(BufferedStompClient $stomp)
    {
        $this->stomp = $stomp;
    }

    /**
     * {@inheritdoc}
     *
     * @return StompMessage
     */
    public function createMessage($body = '', array $properties = [], array $headers = [])
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
        $destination = new StompDestination();
        $destination->setQueueName($name);

        return $destination;
    }

    /**
     * {@inheritdoc}
     *
     * @return StompDestination
     */
    public function createTemporaryQueue()
    {
        $queue = $this->createQueue(UUID::generate());
        $queue->setType(StompDestination::TYPE_TEMP_QUEUE);

        return $queue;
    }

    /**
     * {@inheritdoc}
     *
     * @return StompDestination
     */
    public function createTopic($name)
    {
        $destination = new StompDestination();
        $destination->setQueueName($name);

        return $destination;
    }

    /**
     * {@inheritdoc}
     *
     * @param StompDestination $destination
     *
     * @return StompConsumer
     */
    public function createConsumer(Destination $destination)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, StompDestination::class);

        return new StompConsumer($this->stomp, $destination);
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
    public function close()
    {
        $this->stomp->disconnect();
    }
}
