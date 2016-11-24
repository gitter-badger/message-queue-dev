<?php
namespace Formapro\Fms;

interface Producer
{
    /**
     * @param Destination $destination
     * @param Message     $message
     *
     * @throws \Formapro\Fms\Exception\Exception                   - if the JMS provider fails to send
     *                                                             the message due to some internal error
     * @throws \Formapro\Fms\Exception\InvalidDestinationException - if a client uses
     *                                                             this method with an invalid destination
     * @throws \Formapro\Fms\Exception\InvalidMessageException     - if an invalid message
     *                                                             is specified
     */
    public function send(Destination $destination, Message $message);
}
