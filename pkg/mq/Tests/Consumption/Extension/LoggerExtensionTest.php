<?php
namespace Formapro\MessageQueue\Tests\Consumption\Extension;

use Formapro\Jms\JMSConsumer;
use Formapro\Jms\JMSContext;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\Extension\LoggerExtension;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
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

        $context = new Context($this->createContextMock());

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

        $context = new Context($this->createContextMock());

        $extension->onStart($context);
    }

    public function testShouldLogRejectMessageStatus()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('reason', ['body' => 'message body', 'headers' => [], 'properties' => []])
        ;

        $extension = new LoggerExtension($logger);

        $message = new NullMessage();
        $message->setBody('message body');

        $context = new Context($this->createContextMock());
        $context->setResult(Result::reject('reason'));
        $context->setMessage($message);

        $extension->onPostReceived($context);
    }

    public function testShouldLogRequeueMessageStatus()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('reason', ['body' => 'message body', 'headers' => [], 'properties' => []])
        ;

        $extension = new LoggerExtension($logger);

        $message = new NullMessage();
        $message->setBody('message body');

        $context = new Context($this->createContextMock());
        $context->setResult(Result::requeue('reason'));
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

        $context = new Context($this->createContextMock());
        $context->setResult(Result::requeue());

        $extension->onPostReceived($context);
    }

    public function testShouldLogAckMessageStatus()
    {
        $logger = $this->createLogger();
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('reason', ['body' => 'message body', 'headers' => [], 'properties' => []])
        ;

        $extension = new LoggerExtension($logger);

        $message = new NullMessage();
        $message->setBody('message body');

        $context = new Context($this->createContextMock());
        $context->setResult(Result::ack('reason'));
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

        $context = new Context($this->createContextMock());
        $context->setResult(Result::ack());

        $extension->onPostReceived($context);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSContext
     */
    protected function createContextMock()
    {
        return $this->createMock(JMSContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    protected function createLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSConsumer
     */
    protected function createConsumerMock()
    {
        return $this->createMock(JMSConsumer::class);
    }
}
