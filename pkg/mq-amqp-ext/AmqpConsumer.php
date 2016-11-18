<?php
namespace Formapro\AmqpExt;

use Formapro\Jms\Exception\InvalidMessageException;
use Formapro\Jms\JMSConsumer;
use Formapro\Jms\Message;

class AmqpConsumer implements JMSConsumer
{
    /**
     * @var AmqpQueue
     */
    private $queue;

    /**
     * @var \AMQPQueue
     */
    private $extQueue;

    /**
     * @var \AMQPChannel
     */
    private $extChannel;

    /**
     * @param \AMQPChannel $extChannel
     * @param AmqpQueue    $queue
     */
    public function __construct(\AMQPChannel $extChannel, AmqpQueue $queue)
    {
        $extQueue = new \AMQPQueue($extChannel);
        $extQueue->setName($queue->getQueueName());
        $extQueue->setFlags($queue->getFlags());
        $extQueue->setArguments($queue->getArguments());

        $this->queue = $queue;
        $this->extQueue = $extQueue;
        $this->extChannel = $extChannel;
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
     *
     * @return AmqpMessage|null
     */
    public function receive($timeout = 0)
    {
        $originalTimeout = $this->extChannel->getConnection()->getReadTimeout();
        try {
            $this->extChannel->getConnection()->setReadTimeout($timeout);

            $receivedMessage = null;

            $this->extQueue->consume(function (\AMQPEnvelope $extEnvelope, \AMQPQueue $extQueue) use (&$receivedMessage) {
                $receivedMessage = $this->convertMessage($extEnvelope);

                return false;
            });

            return $receivedMessage;
        } catch (\AMQPQueueException $e) {
            if ('Consumer timeout exceed' == $e->getMessage()) {
                return null;
            }

            throw $e;
        } finally {
            $this->extChannel->getConnection()->setReadTimeout($originalTimeout);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpMessage|null
     */
    public function receiveNoWait()
    {
        if ($extMessage = $this->extQueue->get()) {
            return $this->convertMessage($extMessage);
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

        $this->extQueue->ack($message->getDeliveryTag());
    }

    /**
     * {@inheritdoc}
     *
     * @param AmqpMessage $message
     */
    public function reject(Message $message, $requeue = false)
    {
        InvalidMessageException::assertMessageInstanceOf($message, AmqpMessage::class);

        $this->extQueue->reject(
            $message->getDeliveryTag(),
            $requeue ? AMQP_REQUEUE : AMQP_NOPARAM
        );
    }

    /**
     * @param \AMQPEnvelope $extEnvelope
     *
     * @return AmqpMessage
     */
    private function convertMessage(\AMQPEnvelope $extEnvelope)
    {
        $headers = $extEnvelope->getHeaders();

        $properties = [];
        if (array_key_exists('headers', $headers)) {
            $properties = $headers['headers'];
            unset($headers['headers']);
        }

        $message = new AmqpMessage($extEnvelope->getBody(), $properties, $headers);
        $message->setRedelivered($extEnvelope->isRedelivery());
        $message->setDeliveryTag($extEnvelope->getDeliveryTag());

        return $message;
    }
}
