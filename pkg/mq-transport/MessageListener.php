<?php
namespace Formapro\MessageQueue\Transport;

interface MessageListener
{
    /**
     * @param Message $message
     *
     * @return void
     */
    public function onMessage(Message $message);
}
