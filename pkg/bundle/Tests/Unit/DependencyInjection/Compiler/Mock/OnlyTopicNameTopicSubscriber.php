<?php
namespace Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock;

use Formapro\MessageQueue\Client\TopicSubscriberInterface;

class OnlyTopicNameTopicSubscriber implements TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return ['topic-subscriber-name'];
    }
}
