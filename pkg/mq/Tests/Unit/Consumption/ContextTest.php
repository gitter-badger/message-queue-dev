<?php
namespace Formapro\MessageQueue\Tests\Unit\Consumption;

use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\MessageConsumerInterface;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueue\Transport\SessionInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Psr\Log\NullLogger;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testCouldBeConstructedWithSessionAsFirstArgument()
    {
        new Context($this->createSession());
    }

    public function testShouldAllowGetSessionSetInConstructor()
    {
        $session = $this->createSession();

        $context = new Context($session);

        $this->assertSame($session, $context->getSession());
    }

    public function testShouldAllowGetMessageConsumerPreviouslySet()
    {
        $messageConsumer = $this->createMessageConsumer();
        
        $context = new Context($this->createSession());
        $context->setMessageConsumer($messageConsumer);

        $this->assertSame($messageConsumer, $context->getMessageConsumer());
    }

    public function testThrowOnTryToChangeMessageConsumerIfAlreadySet()
    {
        $messageConsumer = $this->createMessageConsumer();
        $anotherMessageConsumer = $this->createMessageConsumer();

        $context = new Context($this->createSession());

        $context->setMessageConsumer($messageConsumer);

        $this->setExpectedException(IllegalContextModificationException::class);
        $context->setMessageConsumer($anotherMessageConsumer);
    }

    public function testShouldAllowGetMessageProducerPreviouslySet()
    {
        $messageProcessor = $this->createMessageProcessor();

        $context = new Context($this->createSession());
        $context->setMessageProcessor($messageProcessor);

        $this->assertSame($messageProcessor, $context->getMessageProcessor());
    }

    public function testThrowOnTryToChangeMessageProcessorIfAlreadySet()
    {
        $messageProcessor = $this->createMessageProcessor();
        $anotherMessageProcessor = $this->createMessageProcessor();

        $context = new Context($this->createSession());

        $context->setMessageProcessor($messageProcessor);

        $this->setExpectedException(IllegalContextModificationException::class);
        $context->setMessageProcessor($anotherMessageProcessor);
    }

    public function testShouldAllowGetLoggerPreviouslySet()
    {
        $logger = new NullLogger();

        $context = new Context($this->createSession());
        $context->setLogger($logger);

        $this->assertSame($logger, $context->getLogger());
    }

    public function testShouldSetExecutionInterruptedToFalseInConstructor()
    {
        $context = new Context($this->createSession());

        $this->assertFalse($context->isExecutionInterrupted());
    }

    public function testShouldAllowGetPreviouslySetMessage()
    {
        /** @var MessageInterface $message */
        $message = $this->createMock(MessageInterface::class);

        $context = new Context($this->createSession());

        $context->setMessage($message);

        $this->assertSame($message, $context->getMessage());
    }

    public function testThrowOnTryToChangeMessageIfAlreadySet()
    {
        /** @var MessageInterface $message */
        $message = $this->createMock(MessageInterface::class);

        $context = new Context($this->createSession());

        $this->setExpectedException(
            IllegalContextModificationException::class,
            'The message could be set once'
        );

        $context->setMessage($message);
        $context->setMessage($message);
    }

    public function testShouldAllowGetPreviouslySetException()
    {
        $exception = new \Exception();

        $context = new Context($this->createSession());

        $context->setException($exception);

        $this->assertSame($exception, $context->getException());
    }

    public function testShouldAllowGetPreviouslySetStatus()
    {
        $status = 'aStatus';

        $context = new Context($this->createSession());

        $context->setResult($status);

        $this->assertSame($status, $context->getResult());
    }

    public function testThrowOnTryToChangeStatusIfAlreadySet()
    {
        $status = 'aStatus';

        $context = new Context($this->createSession());

        $this->setExpectedException(
            IllegalContextModificationException::class,
            'The status modification is not allowed'
        );

        $context->setResult($status);
        $context->setResult($status);
    }

    public function testShouldAllowGetPreviouslySetExecutionInterrupted()
    {
        $context = new Context($this->createSession());

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        $context->setExecutionInterrupted(true);

        $this->assertTrue($context->isExecutionInterrupted());
    }

    public function testThrowOnTryToRollbackExecutionInterruptedIfAlreadySetToTrue()
    {
        $context = new Context($this->createSession());

        $this->setExpectedException(
            IllegalContextModificationException::class,
            'The execution once interrupted could not be roll backed'
        );

        $context->setExecutionInterrupted(true);
        $context->setExecutionInterrupted(false);
    }

    public function testNotThrowOnSettingExecutionInterruptedToTrueIfAlreadySetToTrue()
    {
        $context = new Context($this->createSession());

        $context->setExecutionInterrupted(true);
        $context->setExecutionInterrupted(true);
    }

    public function testShouldAllowGetPreviouslySetLogger()
    {
        $expectedLogger = new NullLogger();

        $context = new Context($this->createSession());

        $context->setLogger($expectedLogger);

        $this->assertSame($expectedLogger, $context->getLogger());
    }

    public function testThrowOnSettingLoggerIfAlreadySet()
    {
        $context = new Context($this->createSession());

        $context->setLogger(new NullLogger());

        $this->setExpectedException(
            IllegalContextModificationException::class,
            'The logger modification is not allowed'
        );
        $context->setLogger(new NullLogger());
    }

    public function testShouldAllowGetPreviouslySetQueue()
    {
        $context = new Context($this->createSession());

        $context->setQueue($queue = new NullQueue(''));

        $this->assertSame($queue, $context->getQueue());
    }

    public function testThrowOnSettingQueueNameIfAlreadySet()
    {
        $context = new Context($this->createSession());

        $context->setQueue(new NullQueue(''));

        $this->setExpectedException(
            IllegalContextModificationException::class,
            'The queue modification is not allowed'
        );
        $context->setQueue(new NullQueue(''));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    protected function createSession()
    {
        return $this->createMock(SessionInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageConsumerInterface
     */
    protected function createMessageConsumer()
    {
        return $this->createMock(MessageConsumerInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageProcessorInterface
     */
    protected function createMessageProcessor()
    {
        return $this->createMock(MessageProcessorInterface::class);
    }
}
