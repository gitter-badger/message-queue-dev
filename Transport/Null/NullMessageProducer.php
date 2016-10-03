<?php
namespace FormaPro\MessageQueue\Transport\Null;

use FormaPro\MessageQueue\Transport\DestinationInterface;
use FormaPro\MessageQueue\Transport\MessageInterface;
use FormaPro\MessageQueue\Transport\MessageProducerInterface;

class NullMessageProducer implements MessageProducerInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(DestinationInterface $destination, MessageInterface $message)
    {
    }
}
