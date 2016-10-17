<?php
namespace Formapro\MessageQueue\Transport\Null;

use Formapro\MessageQueue\Transport\DestinationInterface;
use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\MessageProducerInterface;

class NullMessageProducer implements MessageProducerInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(DestinationInterface $destination, MessageInterface $message)
    {
    }
}
