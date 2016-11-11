<?php
namespace Formapro\MessageQueue\Transport\Null;

use Formapro\Jms\Destination;
use Formapro\Jms\JMSProducer;
use Formapro\Jms\Message;

class NullMessageProducer implements JMSProducer
{
    /**
     * {@inheritdoc}
     */
    public function send(Destination $destination, Message $message)
    {
    }
}
