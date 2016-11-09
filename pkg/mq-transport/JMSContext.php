<?php
namespace Formapro\MessageQueue\Transport;

interface JMSContext
{
    /**
     * @return Message
     */
    public function createMessage();

    /**
     * @param string $topicName
     *
     * @return Topic
     */
    public function createTopic($topicName);

    /**
     * @param string $queueName
     *
     * @return Queue
     */
    public function createQueue($queueName);

    /**
     * @return JMSProducer
     */
    public function createProducer();

    /**
     * @param Destination $destination
     *
     * @return JMSConsumer
     */
    public function createConsumer(Destination $destination);

    /**
     * @return void
     */
    public function close();
}
