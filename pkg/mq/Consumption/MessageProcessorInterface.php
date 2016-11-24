<?php
namespace Formapro\MessageQueue\Consumption;

use Formapro\Fms\Context;
use Formapro\Fms\Message;

interface MessageProcessorInterface
{
    /**
     * @param Message $message
     * @param Context $context
     *
     * @return string
     */
    public function process(Message $message, Context $context);
}
