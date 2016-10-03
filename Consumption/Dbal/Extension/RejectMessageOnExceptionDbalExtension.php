<?php
namespace FormaPro\MessageQueue\Consumption\Dbal\Extension;

use FormaPro\MessageQueue\Consumption\AbstractExtension;
use FormaPro\MessageQueue\Consumption\Context;

class RejectMessageOnExceptionDbalExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function onInterrupted(Context $context)
    {
        if (! $context->getException()) {
            return;
        }

        if (! $context->getMessage()) {
            return;
        }

        $context->getMessageConsumer()->reject($context->getMessage(), true);

        $context->getLogger()->debug(
            '[RejectMessageOnExceptionDbalExtension] Execution was interrupted and message was rejected'
        );
    }
}
