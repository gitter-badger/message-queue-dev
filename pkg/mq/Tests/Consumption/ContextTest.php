<?php
namespace Formapro\MessageQueue\Tests\Consumption;

use Formapro\Fms\Consumer;
use Formapro\Fms\Context as FMSContext;
use Formapro\Fms\Message;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Psr\Log\NullLogger;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testCouldBeConstructedWithSessionAsFirstArgument()
    {
        new Context($this->createFMSContext());
    }

    public function testShouldAllowGetSessionSetInConstructor()
    {
        $fmsContext = $this->createFMSContext();

        $context = new Context($fmsContext);

        $this->assertSame($fmsContext, $context->getFMSContext());
    }

    public function testShouldAllowGetMessageConsumerPreviouslySet()
    {
        $messageConsumer = $this->createFMSConsumer();

        $context = new Context($this->createFMSContext());
        $context->setFMSConsumer($messageConsumer);

        $this->assertSame($messageConsumer, $context->getFMSConsumer());
    }

    public function testThrowOnTryToChangeMessageConsumerIfAlreadySet()
    {
        $messageConsumer = $this->createFMSConsumer();
        $anotherMessageConsumer = $this->createFMSConsumer();

        $context = new Context($this->createFMSContext());

        $context->setFMSConsumer($messageConsumer);

        $this->expectException(IllegalContextModificationException::class);

        $context->setFMSConsumer($anotherMessageConsumer);
    }

    public function testShouldAllowGetMessageProducerPreviouslySet()
    {
        $messageProcessor = $this->createMessageProcessor();

        $context = new Context($this->createFMSContext());
        $context->setMessageProcessor($messageProcessor);

        $this->assertSame($messageProcessor, $context->getMessageProcessor());
    }

    public function testThrowOnTryToChangeMessageProcessorIfAlreadySet()
    {
        $messageProcessor = $this->createMessageProcessor();
        $anotherMessageProcessor = $this->createMessageProcessor();

        $context = new Context($this->createFMSContext());

        $context->setMessageProcessor($messageProcessor);

        $this->expectException(IllegalContextModificationException::class);

        $context->setMessageProcessor($anotherMessageProcessor);
    }

    public function testShouldAllowGetLoggerPreviouslySet()
    {
        $logger = new NullLogger();

        $context = new Context($this->createFMSContext());
        $context->setLogger($logger);

        $this->assertSame($logger, $context->getLogger());
    }

    public function testShouldSetExecutionInterruptedToFalseInConstructor()
    {
        $context = new Context($this->createFMSContext());

        $this->assertFalse($context->isExecutionInterrupted());
    }

    public function testShouldAllowGetPreviouslySetMessage()
    {
        /** @var Message $message */
        $message = $this->createMock(Message::class);

        $context = new Context($this->createFMSContext());

        $context->setFMSMessage($message);

        $this->assertSame($message, $context->getFMSMessage());
    }

    public function testThrowOnTryToChangeMessageIfAlreadySet()
    {
        /** @var Message $message */
        $message = $this->createMock(Message::class);

        $context = new Context($this->createFMSContext());

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The message could be set once');

        $context->setFMSMessage($message);
        $context->setFMSMessage($message);
    }

    public function testShouldAllowGetPreviouslySetException()
    {
        $exception = new \Exception();

        $context = new Context($this->createFMSContext());

        $context->setException($exception);

        $this->assertSame($exception, $context->getException());
    }

    public function testShouldAllowGetPreviouslySetResult()
    {
        $result = 'aResult';

        $context = new Context($this->createFMSContext());

        $context->setResult($result);

        $this->assertSame($result, $context->getResult());
    }

    public function testThrowOnTryToChangeResultIfAlreadySet()
    {
        $result = 'aResult';

        $context = new Context($this->createFMSContext());

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The result modification is not allowed');

        $context->setResult($result);
        $context->setResult($result);
    }

    public function testShouldAllowGetPreviouslySetExecutionInterrupted()
    {
        $context = new Context($this->createFMSContext());

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        $context->setExecutionInterrupted(true);

        $this->assertTrue($context->isExecutionInterrupted());
    }

    public function testThrowOnTryToRollbackExecutionInterruptedIfAlreadySetToTrue()
    {
        $context = new Context($this->createFMSContext());

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The execution once interrupted could not be roll backed');

        $context->setExecutionInterrupted(true);
        $context->setExecutionInterrupted(false);
    }

    public function testNotThrowOnSettingExecutionInterruptedToTrueIfAlreadySetToTrue()
    {
        $context = new Context($this->createFMSContext());

        $context->setExecutionInterrupted(true);
        $context->setExecutionInterrupted(true);
    }

    public function testShouldAllowGetPreviouslySetLogger()
    {
        $expectedLogger = new NullLogger();

        $context = new Context($this->createFMSContext());

        $context->setLogger($expectedLogger);

        $this->assertSame($expectedLogger, $context->getLogger());
    }

    public function testThrowOnSettingLoggerIfAlreadySet()
    {
        $context = new Context($this->createFMSContext());

        $context->setLogger(new NullLogger());

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The logger modification is not allowed');

        $context->setLogger(new NullLogger());
    }

    public function testShouldAllowGetPreviouslySetQueue()
    {
        $context = new Context($this->createFMSContext());

        $context->setFMSQueue($queue = new NullQueue(''));

        $this->assertSame($queue, $context->getFMSQueue());
    }

    public function testThrowOnSettingQueueNameIfAlreadySet()
    {
        $context = new Context($this->createFMSContext());

        $context->setFMSQueue(new NullQueue(''));

        $this->expectException(IllegalContextModificationException::class);
        $this->expectExceptionMessage('The queue modification is not allowed');

        $context->setFMSQueue(new NullQueue(''));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FMSContext
     */
    protected function createFMSContext()
    {
        return $this->createMock(FMSContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Consumer
     */
    protected function createFMSConsumer()
    {
        return $this->createMock(Consumer::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageProcessorInterface
     */
    protected function createMessageProcessor()
    {
        return $this->createMock(MessageProcessorInterface::class);
    }
}
