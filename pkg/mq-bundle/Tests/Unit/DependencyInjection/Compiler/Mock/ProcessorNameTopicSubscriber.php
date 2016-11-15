<?php
namespace Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock;

use Formapro\MessageQueue\Client\TopicSubscriberInterface;

class ProcessorNameTopicSubscriber implements TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return [
            'topic-subscriber-name' => [
                'processorName' => 'subscriber-processor-name',
            ],
        ];
    }
}
