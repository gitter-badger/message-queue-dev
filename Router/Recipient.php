<?php
namespace FormaPro\MessageQueue\Router;

use FormaPro\MessageQueue\Transport\DestinationInterface;
use FormaPro\MessageQueue\Transport\MessageInterface;

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
