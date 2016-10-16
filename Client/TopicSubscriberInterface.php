<?php
namespace Formapro\MessageQueue\Client;

interface TopicSubscriberInterface
{
    /**
     * * ['topicName']
     * * ['topicName' => ['processorName' => 'processor', 'destinationName' => 'destination']]
     * processorName, destinationName - optional.
     *
     * @return array
     */
    public static function getSubscribedTopics();
}
