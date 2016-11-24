<?php
namespace Formapro\MessageQueue\Consumption;

use Formapro\Fms\Consumer;
use Formapro\Fms\Context as FMSContext;
use Formapro\Fms\Message;
use Formapro\Fms\Queue;
use Formapro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use Psr\Log\LoggerInterface;

class Context
{
    /**
     * @var FMSContext
     */
    private $fmsContext;

    /**
     * @var \Formapro\Fms\Consumer
     */
    private $fmsConsumer;

    /**
     * @var MessageProcessorInterface
     */
    private $messageProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Message
     */
    private $fmsMessage;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var Result|string
     */
    private $result;

    /**
     * @var \Formapro\Fms\Queue
     */
    private $fmsQueue;

    /**
     * @var bool
     */
    private $executionInterrupted;

    /**
     * @param FMSContext $fmsContext
     */
    public function __construct(FMSContext $fmsContext)
    {
        $this->fmsContext = $fmsContext;

        $this->executionInterrupted = false;
    }

    /**
     * @return \Formapro\Fms\Message
     */
    public function getFMSMessage()
    {
        return $this->fmsMessage;
    }

    /**
     * @param Message $fmsMessage
     */
    public function setFMSMessage(Message $fmsMessage)
    {
        if ($this->fmsMessage) {
            throw new IllegalContextModificationException('The message could be set once');
        }

        $this->fmsMessage = $fmsMessage;
    }

    /**
     * @return FMSContext
     */
    public function getFMSContext()
    {
        return $this->fmsContext;
    }

    /**
     * @return Consumer
     */
    public function getFMSConsumer()
    {
        return $this->fmsConsumer;
    }

    /**
     * @param Consumer $fmsConsumer
     */
    public function setFMSConsumer(Consumer $fmsConsumer)
    {
        if ($this->fmsConsumer) {
            throw new IllegalContextModificationException('The message consumer could be set once');
        }

        $this->fmsConsumer = $fmsConsumer;
    }

    /**
     * @return MessageProcessorInterface
     */
    public function getMessageProcessor()
    {
        return $this->messageProcessor;
    }

    /**
     * @param MessageProcessorInterface $messageProcessor
     */
    public function setMessageProcessor(MessageProcessorInterface $messageProcessor)
    {
        if ($this->messageProcessor) {
            throw new IllegalContextModificationException('The message processor could be set once');
        }

        $this->messageProcessor = $messageProcessor;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return Result|string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param Result|string $result
     */
    public function setResult($result)
    {
        if ($this->result) {
            throw new IllegalContextModificationException('The result modification is not allowed');
        }

        $this->result = $result;
    }

    /**
     * @return bool
     */
    public function isExecutionInterrupted()
    {
        return $this->executionInterrupted;
    }

    /**
     * @param bool $executionInterrupted
     */
    public function setExecutionInterrupted($executionInterrupted)
    {
        if (false == $executionInterrupted && $this->executionInterrupted) {
            throw new IllegalContextModificationException('The execution once interrupted could not be roll backed');
        }

        $this->executionInterrupted = $executionInterrupted;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        if ($this->logger) {
            throw new IllegalContextModificationException('The logger modification is not allowed');
        }

        $this->logger = $logger;
    }

    /**
     * @return \Formapro\Fms\Queue
     */
    public function getFMSQueue()
    {
        return $this->fmsQueue;
    }

    /**
     * @param \Formapro\Fms\Queue $fmsQueue
     */
    public function setFMSQueue(Queue $fmsQueue)
    {
        if ($this->fmsQueue) {
            throw new IllegalContextModificationException('The queue modification is not allowed');
        }

        $this->fmsQueue = $fmsQueue;
    }
}
