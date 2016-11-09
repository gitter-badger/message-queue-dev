<?php
namespace Formapro\MessageQueue\Transport\Null;

use Formapro\MessageQueue\Transport\Destination;
use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\MessageProducerInterface;

class NullMessageProducer implements MessageProducerInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(Destination $destination, MessageInterface $message)
    {
    }
}
