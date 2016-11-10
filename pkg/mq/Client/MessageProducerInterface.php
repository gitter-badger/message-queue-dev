<?php
namespace Formapro\MessageQueue\Client;

interface MessageProducerInterface
{
    /**
     * Sends a message to a topic. There are some message processor may be subscribed to a topic.
     *
     * @param string $topic
     * @param string|array|Message $message
     *
     * @return void
     *
     * @throws \Formapro\Jms\Exception\Exception - if the producer fails to send
     * the message due to some internal error.
     */
    public function send($topic, $message);
}
