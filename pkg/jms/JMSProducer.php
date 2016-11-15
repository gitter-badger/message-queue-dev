<?php
namespace Formapro\Jms;

interface JMSProducer
{
    /**
     * @param Destination $destination
     * @param Message     $message
     *
     * @throws \Formapro\Jms\Exception\Exception                   - if the JMS provider fails to send
     *                                                             the message due to some internal error
     * @throws \Formapro\Jms\Exception\InvalidDestinationException - if a client uses
     *                                                             this method with an invalid destination
     * @throws \Formapro\Jms\Exception\InvalidMessageException     - if an invalid message
     *                                                             is specified
     */
    public function send(Destination $destination, Message $message);
}
