<?php
namespace FormaPro\MessageQueue\Consumption;

class MessageStatus
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
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $reason;

    /**
     * @param string $status
     * @param string $reason
     */
    public function __construct($status, $reason = '')
    {
        $this->status = (string) $status;
        $this->reason = (string) $reason;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     *
     * @return MessageStatus
     */
    public static function acknowledge($reason = '')
    {
        return new static(self::ACK, $reason);
    }

    /**
     * @param string $reason
     *
     * @return MessageStatus
     */
    public static function reject($reason)
    {
        return new static(self::REJECT, $reason);
    }

    /**
     * @param string $reason
     *
     * @return MessageStatus
     */
    public static function requeue($reason = '')
    {
        return new static(self::REQUEUE, $reason);
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->status;
    }
}
