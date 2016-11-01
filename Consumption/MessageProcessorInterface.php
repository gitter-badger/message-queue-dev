<?php
namespace Formapro\MessageQueue\Consumption;

use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\SessionInterface;

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
