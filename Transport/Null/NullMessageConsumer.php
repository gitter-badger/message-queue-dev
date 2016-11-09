<?php
namespace Formapro\MessageQueue\Transport\Null;

use Formapro\MessageQueue\Transport\Destination;
use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\MessageConsumerInterface;

class NullMessageConsumer implements MessageConsumerInterface
{
    /**
     * @var Destination
     */
    private $queue;

    /**
     * @param Destination $queue
     */
    public function __construct(Destination $queue)
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
