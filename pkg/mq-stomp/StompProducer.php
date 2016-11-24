<?php
namespace Formapro\Stomp;

use Formapro\Fms\Destination;
use Formapro\Fms\Exception\InvalidDestinationException;
use Formapro\Fms\Exception\InvalidMessageException;
use Formapro\Fms\Message;
use Formapro\Fms\Producer;
use Stomp\Client;
use Stomp\Transport\Message as StompLibMessage;

class StompProducer implements Producer
{
    /**
     * @var Client
     */
    private $stomp;

    /**
     * @param Client $stomp
     */
    public function __construct(Client $stomp)
    {
        $this->stomp = $stomp;
    }

    /**
     * {@inheritdoc}
     *
     * @param StompDestination $destination
     * @param StompMessage     $message
     */
    public function send(Destination $destination, Message $message)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, StompDestination::class);

        InvalidMessageException::assertMessageInstanceOf($message, StompMessage::class);

        $headers = array_merge($message->getHeaders(), $destination->getHeaders());
        $headers = StompHeadersEncoder::encode($headers, $message->getProperties());

        $stompMessage = new StompLibMessage($message->getBody(), $headers);

        $this->stomp->send($destination->getQueueName(), $stompMessage);
    }
}
