<?php
namespace Formapro\MessageQueue\Router;

use Formapro\MessageQueue\Transport\Destination;
use Formapro\MessageQueue\Transport\MessageInterface;

class Recipient
{
    /**
     * @var Destination
     */
    private $destination;
    
    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @param Destination $destination
     * @param MessageInterface $message
     */
    public function __construct(Destination $destination, MessageInterface $message)
    {
        $this->destination = $destination;
        $this->message = $message;
    }

    /**
     * @return Destination
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
