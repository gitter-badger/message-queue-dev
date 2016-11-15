<?php
namespace Formapro\MessageQueue\Transport\Null;

use Formapro\Jms\Destination;
use Formapro\Jms\JMSConsumer;
use Formapro\Jms\Message;

class NullConsumer implements JMSConsumer
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
     */
    public function receive($timeout = 0)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function receiveNoWait()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(Message $message)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function reject(Message $message, $requeue = false)
    {
    }
}
