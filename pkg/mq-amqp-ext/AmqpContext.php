<?php
namespace Formapro\AmqpExt;

use Formapro\Jms\Destination;
use Formapro\Jms\JMSConsumer;
use Formapro\Jms\JMSContext;
use Formapro\Jms\JMSProducer;

class AmqpContext implements JMSContext
{
    /**
     * @var \AMQPChannel
     */
    private $amqpChannel;

    /**
     * @param \AMQPChannel $amqpChannel
     */
    public function __construct(\AMQPChannel $amqpChannel)
    {
        $this->amqpChannel = $amqpChannel;
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpMessage
     */
    public function createMessage($body = '', array $properties = [], array $headers = [])
    {
        return new AmqpMessage($body, $properties, $headers);
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpTopic
     */
    public function createTopic($topicName)
    {
        return new AmqpTopic($topicName);
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpQueue
     */
    public function createQueue($queueName)
    {
        return new AmqpQueue($queueName);
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpQueue
     */
    public function createTemporaryQueue()
    {
        $queue = new AmqpQueue('');
        $queue->setPassive(false);
        $queue->setDurable(false);
        $queue->setExclusive(true);
        $queue->setAutoDelete(false);

        return $queue;
    }

    /**
     * @return JMSProducer
     */
    public function createProducer()
    {
        // TODO: Implement createProducer() method.
    }

    /**
     * @param Destination $destination
     *
     * @return JMSConsumer
     */
    public function createConsumer(Destination $destination)
    {
        // TODO: Implement createConsumer() method.
    }

    public function close()
    {
    }
}
