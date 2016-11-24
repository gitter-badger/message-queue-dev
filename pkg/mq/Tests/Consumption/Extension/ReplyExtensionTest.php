<?php
namespace Formapro\MessageQueue\Tests\Consumption\Extension;

use Formapro\Jms\JMSContext;
use Formapro\Jms\JMSProducer;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\Extension\ReplyExtension;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueue\Transport\Null\NullContext;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullQueue;

class ReplyExtensionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementExtensionInterface()
    {
        $this->assertClassImplements(ExtensionInterface::class, ReplyExtension::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new ReplyExtension();
    }

    public function testShouldDoNothingOnPreReceived()
    {
        $extension = new ReplyExtension();

        $extension->onPreReceived(new Context(new NullContext()));
    }

    public function testShouldDoNothingOnStart()
    {
        $extension = new ReplyExtension();

        $extension->onStart(new Context(new NullContext()));
    }

    public function testShouldDoNothingOnBeforeReceive()
    {
        $extension = new ReplyExtension();

        $extension->onBeforeReceive(new Context(new NullContext()));
    }

    public function testShouldDoNothingOnInterrupted()
    {
        $extension = new ReplyExtension();

        $extension->onInterrupted(new Context(new NullContext()));
    }

    public function testShouldDoNothingIfReceivedMessageNotHaveReplyToSet()
    {
        $extension = new ReplyExtension();

        $context = new Context(new NullContext());
        $context->setMessage(new NullMessage());

        $extension->onPostReceived($context);
    }

    public function testThrowIfResultNotInstanceOfResult()
    {
        $extension = new ReplyExtension();

        $message = new NullMessage();
        $message->setReplyTo('aReplyToQueue');

        $context = new Context(new NullContext());
        $context->setMessage($message);
        $context->setResult('notInstanceOfResult');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('To send a reply an instance of Result class has to returned from a MessageProcessor.');
        $extension->onPostReceived($context);
    }

    public function testThrowIfResultInstanceOfResultButReplyMessageNotSet()
    {
        $extension = new ReplyExtension();

        $message = new NullMessage();
        $message->setReplyTo('aReplyToQueue');

        $context = new Context(new NullContext());
        $context->setMessage($message);
        $context->setResult(Result::ack());

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('To send a reply the Result must contain a reply message.');
        $extension->onPostReceived($context);
    }

    public function testShouldSendReplyMessageToReplyQueueOnPostReceived()
    {
        $extension = new ReplyExtension();

        $message = new NullMessage();
        $message->setReplyTo('aReplyToQueue');
        $message->setCorrelationId('theCorrelationId');

        $replyMessage = new NullMessage();
        $replyMessage->setCorrelationId('theCorrelationId');

        $replyQueue = new NullQueue('aReplyName');

        $producerMock = $this->createMock(JMSProducer::class);
        $producerMock
            ->expects($this->once())
            ->method('send')
            ->with($replyQueue, $replyMessage)
        ;

        $contextMock = $this->createMock(JMSContext::class);
        $contextMock
            ->expects($this->once())
            ->method('createQueue')
            ->willReturn($replyQueue)
        ;
        $contextMock
            ->expects($this->once())
            ->method('createProducer')
            ->willReturn($producerMock)
        ;

        $context = new Context($contextMock);
        $context->setMessage($message);
        $context->setResult(Result::reply($replyMessage));

        $extension->onPostReceived($context);
    }
}
