<?php
namespace Formapro\MessageQueue\Consumption;

use Formapro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\MessageConsumerInterface;
use Formapro\MessageQueue\Transport\Queue;
use Formapro\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

class Context
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var MessageConsumerInterface
     */
    private $messageConsumer;

    /**
     * @var MessageProcessorInterface
     */
    private $messageProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageInterface
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
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        
        $this->executionInterrupted = false;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param MessageInterface $message
     */
    public function setMessage(MessageInterface $message)
    {
        if ($this->message) {
            throw new IllegalContextModificationException('The message could be set once');
        }

        $this->message = $message;
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return MessageConsumerInterface
     */
    public function getMessageConsumer()
    {
        return $this->messageConsumer;
    }

    /**
     * @param MessageConsumerInterface $messageConsumer
     */
    public function setMessageConsumer(MessageConsumerInterface $messageConsumer)
    {
        if ($this->messageConsumer) {
            throw new IllegalContextModificationException('The message consumer could be set once');
        }

        $this->messageConsumer = $messageConsumer;
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