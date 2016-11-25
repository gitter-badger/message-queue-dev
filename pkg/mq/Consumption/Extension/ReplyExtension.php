<?php
namespace Formapro\MessageQueue\Consumption\Extension;

use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\Result;

class ReplyExtension implements ExtensionInterface
{
    use EmptyExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function onPostReceived(Context $context)
    {
        $replyTo = $context->getFMSMessage()->getReplyTo();
        $correlationId = $context->getFMSMessage()->getCorrelationId();
        if (false == $replyTo) {
            return;
        }

        $result = $context->getResult();
        if (false == $result instanceof Result) {
            throw new \LogicException('To send a reply an instance of Result class has to returned from a MessageProcessor.');
        }

        if (false == $result->getReply()) {
            throw new \LogicException('To send a reply the Result must contain a reply message.');
        }

        $replyMessage = clone $result->getReply();
        $replyMessage->setCorrelationId($correlationId);

        $replyQueue = $context->getFMSContext()->createQueue($replyTo);

        $context->getFMSContext()->createProducer()->send($replyQueue, $replyMessage);
    }
}
