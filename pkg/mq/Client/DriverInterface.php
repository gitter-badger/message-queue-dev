<?php
namespace Formapro\MessageQueue\Client;

use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\Queue;

interface DriverInterface
{
    /**
     * @return MessageInterface
     */
    public function createTransportMessage();

    /**
     * @param Queue $queue
     * @param Message $message
     */
    public function send(Queue $queue, Message $message);

    /**
     * @param string $queueName
     *
     * @return Queue
     */
    public function createQueue($queueName);

    /**
     * @return Config
     */
    public function getConfig();
}
