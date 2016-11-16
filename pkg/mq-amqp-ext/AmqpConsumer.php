<?php
namespace Formapro\AmqpExt;

use Formapro\Jms\JMSConsumer;
use FormaPro\MessageQueue\Transport\Exception\InvalidMessageException;
use FormaPro\MessageQueue\Transport\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage as LibAMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class AmqpConsumer implements JMSConsumer
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var AmqpQueue
     */
    private $queue;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @var AmqpMessage
     */
    private $receivedMessage;

    /**
     * @param AMQPChannel $channel
     * @param AmqpQueue   $queue
     */
    public function __construct(AMQPChannel $channel, AmqpQueue $queue)
    {
        $this->channel = $channel;
        $this->queue = $queue;

        $this->initialized = false;
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    public function receive($timeout = 0)
    {
        $this->initConsumer();

        $this->receivedMessage = null;

        try {
            $this->channel->wait(null, false, $timeout);
        } catch (AMQPTimeoutException $e) {
        }

        return $this->receivedMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function receiveNoWait()
    {
        if ($message = $this->channel->basic_get($this->queue->getQueueName())) {
            return $this->convertMessage($message);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param AmqpMessage $message
     */
    public function acknowledge(Message $message)
    {
        InvalidMessageException::assertMessageInstanceOf($message, AmqpMessage::class);

        $this->channel->basic_ack($message->getDeliveryTag());
    }

    /**
     * {@inheritdoc}
     *
     * @param AmqpMessage $message
     */
    public function reject(Message $message, $requeue = false)
    {
        InvalidMessageException::assertMessageInstanceOf($message, AmqpMessage::class);

        $this->channel->basic_reject($message->getDeliveryTag(), $requeue);
    }

    private function initConsumer()
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $callback = function (LibAMQPMessage $message) {
            $this->receivedMessage = $this->convertMessage($message);
        };

        $this->channel->basic_qos(0, 1, false);

        $this->channel->basic_consume(
            $this->queue->getQueueName(),
            $this->queue->getConsumerTag(),
            $this->queue->isNoLocal(),
            $this->queue->isNoAck(),
            $this->queue->isExclusive(),
            $this->queue->isNoWait(),
            $callback
        );
    }

    /**
     * @param LibAMQPMessage $amqpMessage
     *
     * @return AmqpMessage
     */
    private function convertMessage(LibAMQPMessage $amqpMessage)
    {
        $headers = new AMQPTable($amqpMessage->get_properties());
        $headers = $headers->getNativeData();

        $properties = [];
        if (isset($headers['application_headers'])) {
            $properties = $headers['application_headers'];
        }
        unset($headers['application_headers']);

        $message = new AmqpMessage($amqpMessage->getBody(), $properties, $headers);
        $message->setDeliveryTag($amqpMessage->delivery_info['delivery_tag']);
        $message->setRedelivered($amqpMessage->delivery_info['redelivered']);

        return $message;
    }
}
