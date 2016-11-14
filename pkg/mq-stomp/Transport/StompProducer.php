<?php
namespace Formapro\Stomp\Transport;

use Formapro\Jms\Destination;
use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\Exception\InvalidMessageException;
use Formapro\Jms\JMSProducer;
use Formapro\Jms\Message;
use Stomp\Client;
use Stomp\Transport\Message as StompLibMessage;

class StompProducer implements JMSProducer
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
     * @param StompMessage $message
     */
    public function send(Destination $destination, Message $message)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, StompDestination::class);

        InvalidMessageException::assertMessageInstanceOf($message, StompMessage::class);

        $headers = array_merge($message->getHeaders(), $destination->getHeaders());
        $headers = StompHeadersEncoder::encode($headers, $message->getProperties());

        $stompMessage = new StompLibMessage($message->getBody(), $headers);

        $this->stomp->send($destination->getStompName(), $stompMessage);
    }
}
