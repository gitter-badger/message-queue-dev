<?php
namespace FormaPro\MessageQueue\Tests\Unit\Consumption\Extension;

use FormaPro\MessageQueue\Consumption\Context;
use FormaPro\MessageQueue\Consumption\ExtensionInterface;
use FormaPro\MessageQueue\Consumption\Extension\LoggerExtension;
use FormaPro\MessageQueue\Consumption\MessageStatus;
use FormaPro\MessageQueue\Transport\MessageConsumerInterface;
use FormaPro\MessageQueue\Transport\Null\NullMessage;
use FormaPro\MessageQueue\Transport\SessionInterface;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;
use Psr\Log\LoggerInterface;

class LoggerExtensionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementExtensionInterface()
    {
        $this->assertClassImplements(ExtensionInterface::class, LoggerExtension::class);
    }

    public function testCouldBeConstructedWithLoggerAsFirstArgument()
    {
        new LoggerExtension($this->createLogger());
    }

    public function testShouldSetLoggerToContextOnStart()
    {
        $logger = $this->createLogger();

        $extension = new LoggerExtension($logger);

        $context = new Context($this->createSessionMock());

        $extension->onStart($context);

        $this->assertSame($logger, $context->getLogger());
    }

    public function testShouldAddInfoMessageOnStart()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith('Set context\'s logger'))
        ;

        $extension = new LoggerExtension($logger);

        $context = new Context($this->createSessionMock());

        $extension->onStart($context);
    }

    public function testShouldLogRejectMessageStatus()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('reason', ['body' => 'message body'])
        ;

        $extension = new LoggerExtension($logger);

        $message = new NullMessage();
        $message->setBody('message body');

        $context = new Context($this->createSessionMock());
        $context->setStatus(MessageStatus::reject('reason'));
        $context->setMessage($message);

        $extension->onPostReceived($context);
    }

    public function testShouldLogRequeueMessageStatus()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('reason', ['body' => 'message body'])
        ;

        $extension = new LoggerExtension($logger);

        $message = new NullMessage();
        $message->setBody('message body');

        $context = new Context($this->createSessionMock());
        $context->setStatus(MessageStatus::requeue('reason'));
        $context->setMessage($message);

        $extension->onPostReceived($context);
    }

    public function testShouldNotLogRequeueMessageStatusIfReasonIsEmpty()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->never())
            ->method('error')
        ;

        $extension = new LoggerExtension($logger);

        $context = new Context($this->createSessionMock());
        $context->setStatus(MessageStatus::requeue());

        $extension->onPostReceived($context);
    }

    public function testShouldLogAckMessageStatus()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('reason', ['body' => 'message body'])
        ;

        $extension = new LoggerExtension($logger);

        $message = new NullMessage();
        $message->setBody('message body');

        $context = new Context($this->createSessionMock());
        $context->setStatus(MessageStatus::acknowledge('reason'));
        $context->setMessage($message);

        $extension->onPostReceived($context);
    }

    public function testShouldNotLogAckMessageStatusIfReasonIsEmpty()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->never())
            ->method('info')
        ;

        $extension = new LoggerExtension($logger);

        $context = new Context($this->createSessionMock());
        $context->setStatus(MessageStatus::acknowledge());

        $extension->onPostReceived($context);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    protected function createSessionMock()
    {
        return $this->createMock(SessionInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    protected function createLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageConsumerInterface
     */
    protected function createMessageConsumerMock()
    {
        return $this->createMock(MessageConsumerInterface::class);
    }
}
