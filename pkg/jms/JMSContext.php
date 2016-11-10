<?php
namespace Formapro\Jms;

interface JMSContext
{
    /**
     * @param string $body
     * @param array $properties
     * @param array $headers
     *
     * @return Message
     */
    public function createMessage($body = '', array $properties = [], array $headers = []);

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
