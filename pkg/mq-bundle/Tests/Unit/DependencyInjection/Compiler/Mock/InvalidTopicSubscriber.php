<?php
namespace Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock;

use Formapro\MessageQueue\Client\TopicSubscriberInterface;

class InvalidTopicSubscriber implements TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return [12345];
    }
}
