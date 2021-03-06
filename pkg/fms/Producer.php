<?php
namespace Formapro\Fms;

interface Producer
{
    /**
     * @param Destination $destination
     * @param Message     $message
     *
     * @throws Exception                   - if the provider fails to send
     *                                     the message due to some internal error
     * @throws InvalidDestinationException - if a client uses
     *                                     this method with an invalid destination
     * @throws InvalidMessageException     - if an invalid message
     *                                     is specified
     */
    public function send(Destination $destination, Message $message);
}
