<?php
namespace Formapro\MessageQueue\Transport\Null;

use Formapro\Fms\Destination;
use Formapro\Fms\Message;
use Formapro\Fms\Producer;

class NullProducer implements Producer
{
    /**
     * {@inheritdoc}
     */
    public function send(Destination $destination, Message $message)
    {
    }
}
