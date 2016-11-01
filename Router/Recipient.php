<?php
namespace Formapro\MessageQueue\Router;

use Formapro\MessageQueue\Transport\DestinationInterface;
use Formapro\MessageQueue\Transport\MessageInterface;

class Recipient
{
    /**
     * @var DestinationInterface
     */
    private $destination;
    
    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @param DestinationInterface $destination
     * @param MessageInterface $message
     */
    public function __construct(DestinationInterface $destination, MessageInterface $message)
    {
        $this->destination = $destination;
        $this->message = $message;
    }

    /**
     * @return DestinationInterface
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }
}
