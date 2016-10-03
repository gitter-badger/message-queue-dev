<?php
namespace FormaPro\MessageQueue\Transport;

interface MessageProducerInterface
{
    /**
     * @param DestinationInterface $destination
     * @param MessageInterface $message
     *
     * @return void
     *
     * @throws \FormaPro\MessageQueue\Transport\Exception\Exception - if the JMS provider fails to send
     * the message due to some internal error.
     *
     * @throws \FormaPro\MessageQueue\Transport\Exception\InvalidDestinationException - if a client uses
     * this method with an invalid destination.
     *
     * @throws \FormaPro\MessageQueue\Transport\Exception\InvalidMessageException - if an invalid message
     * is specified.
     */
    public function send(DestinationInterface $destination, MessageInterface $message);
}
