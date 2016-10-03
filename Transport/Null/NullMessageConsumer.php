<?php
namespace FormaPro\MessageQueue\Transport\Null;

use FormaPro\MessageQueue\Transport\DestinationInterface;
use FormaPro\MessageQueue\Transport\MessageInterface;
use FormaPro\MessageQueue\Transport\MessageConsumerInterface;

class NullMessageConsumer implements MessageConsumerInterface
{
    /**
     * @var DestinationInterface
     */
    private $queue;

    /**
     * @param DestinationInterface $queue
     */
    public function __construct(DestinationInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     *
     * @return null
     */
    public function receive($timeout = 0)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @return null
     */
    public function receiveNoWait()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(MessageInterface $message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function reject(MessageInterface $message, $requeue = false)
    {
    }
}
