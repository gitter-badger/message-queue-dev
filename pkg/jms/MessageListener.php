<?php
namespace Formapro\Jms;

interface MessageListener
{
    /**
     * @param Message $message
     *
     * @return void
     */
    public function onMessage(Message $message);
}
