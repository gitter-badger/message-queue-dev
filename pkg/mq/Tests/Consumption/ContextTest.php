<?php
namespace Formapro\MessageQueue\Tests\Consumption;

use Formapro\Jms\JMSConsumer;
use Formapro\Jms\JMSContext;
use Formapro\Jms\Message;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Psr\Log\NullLogger;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testCouldBeConstructedWithSessionAsFirstArgument()
    {
        new Context($this->createContext());
    }

    public function testShouldAllowGetSessionSetInConstructor()
    {
        $jmsContext = $this->createContext();

        $context = new Context($jmsContext);

        $this->assertSame($jmsContext, $context->getContext());
    }

    public function testShouldAllowGetMessageConsumerPreviouslySet()
    {
        $messageConsumer = $this->createConsumer();
        
        $context = new Context($this->createContext());
        $context->setConsumer($messageConsumer);

        $this->assertSame($messageConsumer, $context->getConsumer());
    }

    public function testThrowOnTryToChangeMessageConsumerIfAlreadySet()
    {
        $messageConsumer = $this->createConsumer();
        $anotherMessageConsumer = $this->createConsumer();

        $context = new Context($this->createContext());

        $context->setConsumer($messageConsumer);

        $this->expectException(IllegalContextModificationException::class);

        $context->setConsumer($anotherMessageConsumer);
    }

    public function testShouldAllowGetMessageProducerPreviouslySet()
    {
        $messageProcessor = $this->createMessageProcessor();

        $context = new Context($this->createContext());
        $context->setMessageProcessor($messageProcessor);

        $this->assertSame($messageProcessor, $context->getMessageProcessor());
    }

    public function testThrowOnTryToChangeMessageProcessorIfAlreadySet()
    {
        $messageProcessor = $this->createMessageProcessor();
        $anotherMessageProcessor = $this->createMessageProcessor();

        $context = new Context($this->createContext());

        $context->setMessageProcessor($messageProcessor);

        $this->expectException(IllegalContextModificationException::class);

        $context->setMessageProcessor($anotherMessageProcessor);
    }

    public function testShouldAllowGetLoggerPreviouslySet()
    {
        $logger = new NullLogger();

        $context = new Context($this->createContext());
        $context->setLogger($logger);

        $this->assertSame($logger, $context->getLogger());
    }

    public function testShouldSetExecutionInterruptedToFalseInConstructor()
    {
        $context = new Context($this->createContext());

        $this->assertFalse($context->isExecutionInterrupted());
    }

    public function testShouldAllowGetPreviouslySetMessage()
    {
        /** @var Message $message */
        $message = $this->createMock(Message::class);

        $context = new Context($this->createContext());

        $context->setMessage($message);

        $this->assertSame($message, $context->getMessage());
    }

    public function testThrowOnTryToChangeMessageIfAlreadySet()
    {
        /** @var Message $message */
        $message = $this->createMock(Message::class);

        $context = new Context($this->createContext());

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The message could be set once');

        $context->setMessage($message);
        $context->setMessage($message);
    }

    public function testShouldAllowGetPreviouslySetException()
    {
        $exception = new \Exception();

        $context = new Context($this->createContext());

        $context->setException($exception);

        $this->assertSame($exception, $context->getException());
    }

    public function testShouldAllowGetPreviouslySetStatus()
    {
        $status = 'aStatus';

        $context = new Context($this->createContext());

        $context->setResult($status);

        $this->assertSame($status, $context->getResult());
    }

    public function testThrowOnTryToChangeStatusIfAlreadySet()
    {
        $status = 'aStatus';

        $context = new Context($this->createContext());

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The status modification is not allowed');

        $context->setResult($status);
        $context->setResult($status);
    }

    public function testShouldAllowGetPreviouslySetExecutionInterrupted()
    {
        $context = new Context($this->createContext());

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        $context->setExecutionInterrupted(true);

        $this->assertTrue($context->isExecutionInterrupted());
    }

    public function testThrowOnTryToRollbackExecutionInterruptedIfAlreadySetToTrue()
    {
        $context = new Context($this->createContext());

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The execution once interrupted could not be roll backed');

        $context->setExecutionInterrupted(true);
        $context->setExecutionInterrupted(false);
    }

    public function testNotThrowOnSettingExecutionInterruptedToTrueIfAlreadySetToTrue()
    {
        $context = new Context($this->createContext());

        $context->setExecutionInterrupted(true);
        $context->setExecutionInterrupted(true);
    }

    public function testShouldAllowGetPreviouslySetLogger()
    {
        $expectedLogger = new NullLogger();

        $context = new Context($this->createContext());

        $context->setLogger($expectedLogger);

        $this->assertSame($expectedLogger, $context->getLogger());
    }

    public function testThrowOnSettingLoggerIfAlreadySet()
    {
        $context = new Context($this->createContext());

        $context->setLogger(new NullLogger());

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The logger modification is not allowed');

        $context->setLogger(new NullLogger());
    }

    public function testShouldAllowGetPreviouslySetQueue()
    {
        $context = new Context($this->createContext());

        $context->setQueue($queue = new NullQueue(''));

        $this->assertSame($queue, $context->getQueue());
    }

    public function testThrowOnSettingQueueNameIfAlreadySet()
    {
        $context = new Context($this->createContext());

        $context->setQueue(new NullQueue(''));

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The queue modification is not allowed');

        $context->setQueue(new NullQueue(''));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSContext
     */
    protected function createContext()
    {
        return $this->createMock(JMSContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSConsumer
     */
    protected function createConsumer()
    {
        return $this->createMock(JMSConsumer::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageProcessorInterface
     */
    protected function createMessageProcessor()
    {
        return $this->createMock(MessageProcessorInterface::class);
    }
}
