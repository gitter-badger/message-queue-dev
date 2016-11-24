<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\Fms\Context;
use Formapro\Fms\Message;
use Formapro\MessageQueue\Client\TopicSubscriberInterface;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\Result;

class TestMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    const TOPIC = 'test-topic';

    /**
     * @var Message
     */
    public $message;

    public function process(Message $message, Context $context)
    {
        $this->message = $message;

        return Result::ACK;
    }

    public static function getSubscribedTopics()
    {
        return [self::TOPIC];
    }
}
