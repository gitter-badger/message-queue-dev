<?php
namespace FormaPro\MessageQueue\Consumption;

use FormaPro\MessageQueue\Transport\MessageInterface;
use FormaPro\MessageQueue\Transport\SessionInterface;

interface MessageProcessorInterface
{
    /**
     * @param MessageInterface $message
     * @param SessionInterface $session
     *
     * @return string
     */
    public function process(MessageInterface $message, SessionInterface $session);
}
