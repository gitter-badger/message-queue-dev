<?php
namespace Formapro\MessageQueue\Transport;

interface SessionInterface
{
    /**
     * @param string $body
     * @param array  $properties
     * @param array  $headers
     *
     * @return MessageInterface
     */
    public function createMessage($body = null, array $properties = [], array $headers = []);

    /**
     * @param string $name
     *
     * @return Queue
     */
    public function createQueue($name);

    /**
     * @param string $name
     *
     * @return Topic
     */
    public function createTopic($name);

    /**
     * @param Destination $destination
     *
     * @return MessageConsumerInterface
     */
    public function createConsumer(Destination $destination);

    /**
     * @return MessageProducerInterface
     */
    public function createProducer();

    /**
     * @param Destination $destination
     */
    public function declareTopic(Destination $destination);

    /**
     * @param Destination $destination
     */
    public function declareQueue(Destination $destination);

    /**
     * @param Destination $source
     * @param Destination $target
     */
    public function declareBind(Destination $source, Destination $target);
    
    public function close();
}
