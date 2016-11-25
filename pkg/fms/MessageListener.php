<?php
namespace Formapro\Fms;

interface MessageListener
{
    /**
     * @param Message $message
     */
    public function onMessage(Message $message);
}
