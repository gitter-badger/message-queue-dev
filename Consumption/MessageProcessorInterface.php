<?php
namespace Formapro\MessageQueue\Consumption;

use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\SessionInterface;

interface MessageProcessorInterface
{
    /**
     * Use this constant when the message is processed successfully and the message could be removed from the queue.
     */
    const ACK = 'formapro.message_queue.consumption.ack';

    /**
     * Use this constant when the message is not valid or could not be processed
     * The message is removed from the queue
     */
    const REJECT = 'formapro.message_queue.consumption.reject';

    /**
     * Use this constant when the message is not valid or could not be processed right now but we can try again later
     * The original message is removed from the queue but a copy is publsihed to the queue again.
     */
    const REQUEUE = 'formapro.message_queue.consumption.requeue';

    /**
     * @param MessageInterface $message
     * @param SessionInterface $session
     *
     * @return string
     */
    public function process(MessageInterface $message, SessionInterface $session);
}
