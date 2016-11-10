<?php
namespace Formapro\MessageQueue\Transport;

interface MessageProducerInterface
{
    /**
     * @param Destination $destination
     * @param MessageInterface $message
     *
     * @return void
     *
     * @throws \Formapro\Jms\Exception\Exception - if the JMS provider fails to send
     * the message due to some internal error.
     *
     * @throws \Formapro\Jms\Exception\InvalidDestinationException - if a client uses
     * this method with an invalid destination.
     *
     * @throws \Formapro\Jms\Exception\InvalidMessageException - if an invalid message
     * is specified.
     */
    public function send(Destination $destination, MessageInterface $message);
}
