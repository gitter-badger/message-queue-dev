<?php
namespace Formapro\MessageQueue\Rpc;

use Formapro\Jms\JMSConsumer;

class Promise
{
    /**
     * @var JMSConsumer
     */
    private $consumer;

    /**
     * @var int
     */
    private $timeout;
    /**
     * @var string
     */
    private $correlationId;

    /**
     * @param JMSConsumer $consumer
     * @param string $correlationId
     * @param int $timeout
     */
    public function __construct(JMSConsumer $consumer, $correlationId, $timeout)
    {
        $this->consumer = $consumer;
        $this->timeout = $timeout;
        $this->correlationId = $correlationId;
    }

    public function getMessage()
    {
        $endTime = time() + $this->timeout;

        while (time() < $endTime) {
            if ($message = $this->consumer->receive($this->timeout)) {
                if ($message->getCorrelationId() === $this->correlationId) {
                    $this->consumer->acknowledge($message);

                    return $message;
                } else {
                    $this->consumer->reject($message, true);
                }
            }
        }

        throw new \LogicException(sprintf('Time outed without receiving reply message. Timeout: %s, CorrelationId: %s', $this->timeout, $this->correlationId));
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}
