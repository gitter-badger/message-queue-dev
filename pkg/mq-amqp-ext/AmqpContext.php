<?php
namespace Formapro\AmqpExt;

use Formapro\Jms\Destination;
use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\JMSContext;

class AmqpContext implements JMSContext
{
    /**
     * @var \AMQPConnection
     */
    private $amqpConnection;

    /**
     * @var \AMQPChannel
     */
    private $amqpChannel;

    /**
     * @param \AMQPConnection $amqpConnection
     */
    public function __construct(\AMQPConnection $amqpConnection)
    {
        $this->amqpConnection = $amqpConnection;
        $this->amqpChannel = new \AMQPChannel($amqpConnection);
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
     * @param AmqpTopic|Destination $destination
     */
    public function deleteTopic(Destination $destination)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, AmqpTopic::class);

        $amqpExchange = new \AMQPExchange($this->amqpChannel);
        $amqpExchange->delete($destination->getTopicName(), $destination->getFlags());
    }

    /**
     * @param AmqpTopic|Destination $destination
     */
    public function declareTopic(Destination $destination)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, AmqpTopic::class);

        $amqpExchange = new \AMQPExchange($this->amqpChannel);
        $amqpExchange->setName($destination->getTopicName());
        $amqpExchange->setType($destination->getType());
        $amqpExchange->setArguments($destination->getArguments());
        $amqpExchange->setFlags($destination->getFlags());

        $amqpExchange->declareExchange();
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
     * @param AmqpQueue|Destination $destination
     */
    public function deleteQueue(Destination $destination)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, AmqpQueue::class);

        $amqpQueue = new \AMQPQueue($this->amqpChannel);
        $amqpQueue->setName($destination->getQueueName());
        $amqpQueue->delete($destination->getFlags());
    }

    /**
     * @param AmqpQueue|Destination $destination
     */
    public function declareQueue(Destination $destination)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, AmqpQueue::class);

        $amqpQueue = new \AMQPQueue($this->amqpChannel);
        $amqpQueue->setName($destination->getQueueName());
        $amqpQueue->setFlags($destination->getFlags());
        $amqpQueue->setArguments($destination->getArguments());

        $amqpQueue->declareQueue();
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
     * {@inheritdoc}
     *
     * @return AmqpProducer
     */
    public function createProducer()
    {
        return new AmqpProducer($this->amqpChannel);
    }

    /**
     * {@inheritdoc}
     *
     * @param Destination|AmqpQueue $destination
     *
     * @return AmqpConsumer
     */
    public function createConsumer(Destination $destination)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, AmqpQueue::class);

        return new AmqpConsumer($this->amqpChannel, $destination);
    }

    public function close()
    {
        if ($this->amqpConnection->isConnected()) {
            $this->amqpConnection->isPersistent() ?
                $this->amqpConnection->disconnect() :
                $this->amqpConnection->pdisconnect();
        }
    }

    /**
     * @param AmqpTopic|Destination $source
     * @param AmqpQueue|Destination $target
     */
    public function bind(Destination $source, Destination $target)
    {
        InvalidDestinationException::assertDestinationInstanceOf($source, AmqpTopic::class);
        InvalidDestinationException::assertDestinationInstanceOf($target, AmqpQueue::class);

        $amqpQueue = new \AMQPQueue($this->amqpChannel);
        $amqpQueue->setName($target->getQueueName());
        $amqpQueue->bind($source->getTopicName(), $amqpQueue->getName(), $target->getBindArguments());
    }

    /**
     * @return \AMQPConnection
     */
    public function getAmqpExtConnection()
    {
        return $this->amqpConnection;
    }

    /**
     * @return mixed
     */
    public function getAmqpExtChannel()
    {
        return $this->amqpChannel;
    }
}
