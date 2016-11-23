<?php
namespace Formapro\MessageQueue\Client;

use Formapro\Jms\Message as TransportMessage;
use Formapro\Jms\Queue;

interface DriverInterface
{
    /**
     * @param Message $message
     *
     * @return TransportMessage
     */
    public function createTransportMessage(Message $message);

    /**
     * @param TransportMessage $message
     *
     * @return Message
     */
    public function createClientMessage(TransportMessage $message);

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
