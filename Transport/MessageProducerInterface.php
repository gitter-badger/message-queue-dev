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
     * @throws \Formapro\MessageQueue\Transport\Exception\Exception - if the JMS provider fails to send
     * the message due to some internal error.
     *
     * @throws \Formapro\MessageQueue\Transport\Exception\InvalidDestinationException - if a client uses
     * this method with an invalid destination.
     *
     * @throws \Formapro\MessageQueue\Transport\Exception\InvalidMessageException - if an invalid message
     * is specified.
     */
    public function send(Destination $destination, MessageInterface $message);
}
