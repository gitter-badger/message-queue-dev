<?php
namespace FormaPro\MessageQueue\Transport\Dbal;

use FormaPro\MessageQueue\Transport\QueueInterface;
use FormaPro\MessageQueue\Transport\TopicInterface;

class DbalDestination implements TopicInterface, QueueInterface
{
    /**
     * @var string
     */
    private $destinationName;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->destinationName = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->destinationName;
    }

    /**
     * {@inheritdoc}
     */
    public function getTopicName()
    {
        return $this->destinationName;
    }
}
