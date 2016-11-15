<?php
namespace Formapro\MessageQueue\Rpc;

use Formapro\Jms\Destination;
use Formapro\Jms\JMSContext;
use Formapro\Jms\Message;
use Formapro\MessageQueue\Util\UUID;

class RpcClient
{
    /**
     * @var JMSContext
     */
    private $context;

    /**
     * @param JMSContext $context
     */
    public function __construct(JMSContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param Destination $destination
     * @param Message $message
     * @param $timeout
     *
     * @return Message
     */
    public function call(Destination $destination, Message $message, $timeout)
    {
        return $this->callAsync($destination, $message, $timeout)->getMessage();
    }

    /**
     * @param Destination $destination
     * @param Message $message
     * @param $timeout
     *
     * @return Promise
     */
    public function callAsync(Destination $destination, Message $message, $timeout)
    {
        if ($timeout < 1) {
            throw new \InvalidArgumentException(sprintf('Timeout must be positive not zero integer. Got %s', $timeout));
        }

        if ($message->getReplyTo()) {
            $replyQueue = $this->context->createQueue($message->getReplyTo());
        } else {
            $replyQueue = $this->context->createTemporaryQueue();
            $message->setReplyTo($replyQueue->getQueueName());
        }

        if (false == $message->getCorrelationId()) {
            $message->setCorrelationId(UUID::generate());
        }

        $this->context->createProducer()->send($destination, $message);

        return new Promise(
            $this->context->createConsumer($replyQueue),
            $message->getCorrelationId(),
            $timeout
        );
    }
}
