<?php
namespace Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock;

use Formapro\MessageQueue\Client\TopicSubscriberInterface;

class DestinationNameTopicSubscriber implements TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return [
            'topic-subscriber-name' => [
                'destinationName' => 'subscriber-destination-name'
            ],
        ];
    }
}
