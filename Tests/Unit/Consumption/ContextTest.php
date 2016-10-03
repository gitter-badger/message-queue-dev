<?php
namespace FormaPro\MessageQueue\Tests\Unit\Consumption;

use FormaPro\MessageQueue\Consumption\Context;
use FormaPro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use FormaPro\MessageQueue\Consumption\MessageProcessorInterface;
use FormaPro\MessageQueue\Transport\MessageInterface;
use FormaPro\MessageQueue\Transport\MessageConsumerInterface;
use FormaPro\MessageQueue\Transport\SessionInterface;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;
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

        $context->setStatus($status);

        $this->assertSame($status, $context->getStatus());
    }

    public function testThrowOnTryToChangeStatusIfAlreadySet()
    {
        $status = 'aStatus';

        $context = new Context($this->createSession());

        $this->setExpectedException(
            IllegalContextModificationException::class,
            'The status modification is not allowed'
        );

        $context->setStatus($status);
        $context->setStatus($status);
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

    public function testShouldAllowGetPreviouslySetQueueName()
    {
        $context = new Context($this->createSession());

        $context->setQueueName('theQueueName');

        $this->assertSame('theQueueName', $context->getQueueName());
    }

    public function testThrowOnSettingQueueNameIfAlreadySet()
    {
        $context = new Context($this->createSession());

        $context->setQueueName('theQueueName');

        $this->setExpectedException(
            IllegalContextModificationException::class,
            'The queueName modification is not allowed'
        );
        $context->setQueueName('theAnotherQueueName');
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
