<?php
namespace Formapro\MessageQueue\Consumption;

use Formapro\Jms\JMSContext;
use Formapro\Jms\Message;

interface MessageProcessorInterface
{
    /**
     * @param Message $message
     * @param JMSContext $context
     *
     * @return string
     */
    public function process(Message $message, JMSContext $context);
}
