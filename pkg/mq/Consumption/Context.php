<?php
namespace Formapro\MessageQueue\Consumption;

use Formapro\Jms\JMSConsumer;
use Formapro\Jms\JMSContext;
use Formapro\Jms\Message;
use Formapro\Jms\Queue;
use Formapro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use Psr\Log\LoggerInterface;

class Context
{
    /**
     * @var JMSContext
     */
    private $context;

    /**
     * @var JMSConsumer
     */
    private $consumer;

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
    private $message;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var string
     */
    private $result;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var boolean
     */
    private $executionInterrupted;

    /**
     * @param JMSContext $context
     */
    public function __construct(JMSContext $context)
    {
        $this->context = $context;
        
        $this->executionInterrupted = false;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Message $message
     */
    public function setMessage(Message $message)
    {
        if ($this->message) {
            throw new IllegalContextModificationException('The message could be set once');
        }

        $this->message = $message;
    }

    /**
     * @return JMSContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return JMSConsumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * @param JMSConsumer $consumer
     */
    public function setConsumer(JMSConsumer $consumer)
    {
        if ($this->consumer) {
            throw new IllegalContextModificationException('The message consumer could be set once');
        }

        $this->consumer = $consumer;
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
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        if ($this->result) {
            throw new IllegalContextModificationException('The status modification is not allowed');
        }

        $this->result = $result;
    }

    /**
     * @return boolean
     */
    public function isExecutionInterrupted()
    {
        return $this->executionInterrupted;
    }

    /**
     * @param boolean $executionInterrupted
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
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param Queue $queue
     */
    public function setQueue(Queue $queue)
    {
        if ($this->queue) {
            throw new IllegalContextModificationException('The queue modification is not allowed');
        }

        $this->queue = $queue;
    }
}
