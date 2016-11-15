<?php
namespace Formapro\MessageQueue\Client;

use Formapro\Jms\Message as JMSMessage;
use Formapro\Jms\Queue;

interface DriverInterface
{
    /**
     * @return JMSMessage
     */
    public function createTransportMessage();

    /**
     * @param Queue   $queue
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
