<?php
namespace Formapro\MessageQueue\Transport\Null;

use Formapro\MessageQueue\Transport\Destination;
use Formapro\MessageQueue\Transport\SessionInterface;

class NullSession implements SessionInterface
{
    /**
     * {@inheritdoc}
     *
     * @return NullMessage
     */
    public function createMessage($body = null, array $properties = [], array $headers = [])
    {
        $message = new NullMessage();
        $message->setBody($body);
        $message->setProperties($properties);
        $message->setHeaders($headers);

        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * @return NullQueue
     */
    public function createQueue($name)
    {
        return new NullQueue($name);
    }

    /**
     * {@inheritdoc}
     *
     * @return NullTopic
     */
    public function createTopic($name)
    {
        return new NullTopic($name);
    }

    /**
     * {@inheritdoc}
     *
     * @return NullConsumer
     */
    public function createConsumer(Destination $destination)
    {
        return new NullConsumer($destination);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createProducer()
    {
        return new NullMessageProducer();
    }

    /**
     * {@inheritdoc}
     */
    public function declareTopic(Destination $destination)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function declareQueue(Destination $destination)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function declareBind(Destination $source, Destination $target)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }
}
