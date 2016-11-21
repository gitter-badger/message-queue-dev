<?php
namespace Formapro\AmqpExt;

use Formapro\Jms\Exception\InvalidMessageException;
use Formapro\Jms\JMSConsumer;
use Formapro\Jms\Message;
use Formapro\MessageQueue\Util\UUID;

class AmqpConsumer implements JMSConsumer
{
    /**
     * @var AmqpContext
     */
    private $context;

    /**
     * @var AmqpQueue
     */
    private $queue;

    /**
     * @var \AMQPQueue
     */
    private $extQueue;

    /**
     * @var string
     */
    private $consumerId;

    /**
     * @var bool
     */
    private $isInit;

    /**
     * @param AmqpContext $context
     * @param AmqpQueue $queue
     */
    public function __construct(AmqpContext $context, AmqpQueue $queue)
    {
        $this->queue = $queue;
        $this->context = $context;

        $this->consumerId = UUID::generate();
        $this->isInit = false;

        $extQueue = new \AMQPQueue($this->context->getAmqpExtChannel());
        $extQueue->setName($this->queue->getQueueName());
        $extQueue->setFlags($this->queue->getFlags());
        $extQueue->setArguments($this->queue->getArguments());
        $this->extQueue = $extQueue;
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
        /** @var \AMQPQueue $extQueue */
        $extConnection = $this->extQueue->getChannel()->getConnection();

        $originalTimeout = $extConnection->getReadTimeout();
        try {
            $extConnection->setReadTimeout($timeout);

            if (false == $this->isInit) {
                $this->extQueue->consume(null, AMQP_NOPARAM, $this->consumerId);

                $this->isInit = true;
            }

            $message = null;

            $this->extQueue->consume(function(\AMQPEnvelope $extEnvelope, \AMQPQueue $q) use (&$message) {
                $message = $this->convertMessage($extEnvelope);

                return false;
            }, AMQP_JUST_CONSUME);

            return $message;
        } catch (\AMQPQueueException $e) {
            if ('Consumer timeout exceed' == $e->getMessage()) {
                return null;
            }

            throw $e;
        } finally {
            $extConnection->setReadTimeout($originalTimeout);
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
        $message = new AmqpMessage(
            $extEnvelope->getBody(),
            $extEnvelope->getHeaders(),
            [
                'message_id' => $extEnvelope->getMessageId(),
                'correlation_id' => $extEnvelope->getCorrelationId(),
                'app_id' => $extEnvelope->getAppId(),
                'type' => $extEnvelope->getType(),
                'content_encoding' => $extEnvelope->getContentEncoding(),
                'content_type' => $extEnvelope->getContentType(),
                'expiration' => $extEnvelope->getExpiration(),
                'priority' => $extEnvelope->getPriority(),
                'reply_to' => $extEnvelope->getReplyTo(),
                'timestamp' => $extEnvelope->getTimeStamp(),
                'user_id' => $extEnvelope->getUserId(),
            ]
        );
        $message->setRedelivered($extEnvelope->isRedelivery());
        $message->setDeliveryTag($extEnvelope->getDeliveryTag());

        return $message;
    }
}
