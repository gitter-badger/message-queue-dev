<?php
namespace Formapro\MessageQueue\Jms;

interface MessageListener
{
    /**
     * @param Message $message
     *
     * @return void
     */
    public function onMessage(Message $message);
}
