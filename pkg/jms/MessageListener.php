<?php
namespace Formapro\Jms;

interface MessageListener
{
    /**
     * @param Message $message
     */
    public function onMessage(Message $message);
}
