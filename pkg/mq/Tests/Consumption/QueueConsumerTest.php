<?php
namespace Formapro\MessageQueue\Tests\Consumption;

use Formapro\Fms\Consumer;
use Formapro\Fms\Context as FMSContext;
use Formapro\Fms\Message;
use Formapro\Fms\Queue;
use Formapro\MessageQueue\Consumption\ChainExtension;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Tests\Consumption\Mock\BreakCycleExtension;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Psr\Log\NullLogger;

class QueueConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithConnectionAndExtensionsAsArguments()
    {
        new QueueConsumer($this->createFMSContextStub(), null, 0);
    }

    public function testCouldBeConstructedWithConnectionOnly()
    {
        new QueueConsumer($this->createFMSContextStub());
    }

    public function testCouldBeConstructedWithConnectionAndSingleExtension()
    {
        new QueueConsumer($this->createFMSContextStub(), $this->createExtension());
    }

    public function testShouldSetEmptyArrayToBoundMessageProcessorsPropertyInConstructor()
    {
        $consumer = new QueueConsumer($this->createFMSContextStub(), null, 0);

        $this->assertAttributeSame([], 'boundMessageProcessors', $consumer);
    }

    public function testShouldAllowGetConnectionSetInConstructor()
    {
        $expectedConnection = $this->createFMSContextStub();

        $consumer = new QueueConsumer($expectedConnection, null, 0);

        $this->assertSame($expectedConnection, $consumer->getFmsContext());
    }

    public function testThrowIfQueueNameEmptyOnBind()
    {
        $messageProcessorMock = $this->createMessageProcessorMock();

        $consumer = new QueueConsumer($this->createFMSContextStub(), null, 0);

        $this->setExpectedException(\LogicException::class, 'The queue name must be not empty.');
        $consumer->bind(new NullQueue(''), $messageProcessorMock);
    }

    public function testThrowIfQueueAlreadyBoundToMessageProcessorOnBind()
    {
        $messageProcessorMock = $this->createMessageProcessorMock();

        $consumer = new QueueConsumer($this->createFMSContextStub(), null, 0);

        $consumer->bind(new NullQueue('theQueueName'), $messageProcessorMock);

        $this->setExpectedException(\LogicException::class, 'The queue was already bound.');
        $consumer->bind(new NullQueue('theQueueName'), $messageProcessorMock);
    }

    public function testShouldAllowBindMessageProcessorToQueue()
    {
        $queue = new NullQueue('theQueueName');
        $messageProcessorMock = $this->createMessageProcessorMock();

        $consumer = new QueueConsumer($this->createFMSContextStub(), null, 0);

        $consumer->bind($queue, $messageProcessorMock);

        $this->assertAttributeSame(['theQueueName' => [$queue, $messageProcessorMock]], 'boundMessageProcessors', $consumer);
    }

    public function testShouldReturnSelfOnBind()
    {
        $messageProcessorMock = $this->createMessageProcessorMock();

        $consumer = new QueueConsumer($this->createFMSContextStub(), null, 0);

        $this->assertSame($consumer, $consumer->bind(new NullQueue('aQueueName'), $messageProcessorMock));
    }

    public function testShouldSubscribeToGivenQueueAndQuitAfterFifthIdleCycle()
    {
        $expectedQueue = new NullQueue('theQueueName');

        $messageConsumerMock = $this->createMock(Consumer::class);
        $messageConsumerMock
            ->expects($this->exactly(5))
            ->method('receive')
            ->willReturn(null)
        ;

        $contextMock = $this->createMock(FMSContext::class);
        $contextMock
            ->expects($this->once())
            ->method('createConsumer')
            ->with($this->identicalTo($expectedQueue))
            ->willReturn($messageConsumerMock)
        ;

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->never())
            ->method('process')
        ;

        $queueConsumer = new QueueConsumer($contextMock, new BreakCycleExtension(5), 0);
        $queueConsumer->bind($expectedQueue, $messageProcessorMock);
        $queueConsumer->consume();
    }

    public function testShouldProcessFiveMessagesAndQuit()
    {
        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->exactly(5))
            ->method('process')
            ->willReturn(Result::ACK)
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(5), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldAckMessageIfMessageProcessorReturnSuchStatus()
    {
        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);
        $messageConsumerStub
            ->expects($this->once())
            ->method('acknowledge')
            ->with($this->identicalTo($messageMock))
        ;

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn(Result::ACK)
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testThrowIfMessageProcessorReturnNull()
    {
        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn(null)
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $this->setExpectedException(\LogicException::class, 'Status is not supported');
        $queueConsumer->consume();
    }

    public function testShouldRejectMessageIfMessageProcessorReturnSuchStatus()
    {
        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);
        $messageConsumerStub
            ->expects($this->once())
            ->method('reject')
            ->with($this->identicalTo($messageMock), false)
        ;

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn(Result::REJECT)
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldRequeueMessageIfMessageProcessorReturnSuchStatus()
    {
        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);
        $messageConsumerStub
            ->expects($this->once())
            ->method('reject')
            ->with($this->identicalTo($messageMock), true)
        ;

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn(Result::REQUEUE)
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testThrowIfMessageProcessorReturnInvalidStatus()
    {
        $this->setExpectedException(\LogicException::class, 'Status is not supported: invalidStatus');

        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn('invalidStatus')
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldNotPassMessageToMessageProcessorIfItWasProcessedByExtension()
    {
        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onPreReceived')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) {
                $context->setResult(Result::ACK);
            })
        ;

        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->never())
            ->method('process')
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnStartExtensionMethod()
    {
        $messageConsumerStub = $this->createMessageConsumerStub($message = null);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onStart')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertNull($context->getFMSConsumer());
                $this->assertNull($context->getMessageProcessor());
                $this->assertNull($context->getLogger());
                $this->assertNull($context->getFMSMessage());
                $this->assertNull($context->getException());
                $this->assertNull($context->getResult());
                $this->assertNull($context->getFMSQueue());
                $this->assertFalse($context->isExecutionInterrupted());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnIdleExtensionMethod()
    {
        $messageConsumerStub = $this->createMessageConsumerStub($message = null);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onIdle')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertSame($messageConsumerStub, $context->getFMSConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getFMSMessage());
                $this->assertNull($context->getException());
                $this->assertNull($context->getResult());
                $this->assertFalse($context->isExecutionInterrupted());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnBeforeReceiveExtensionMethod()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorStub();

        $queue = new NullQueue('aQueueName');

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onBeforeReceive')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage,
                $queue
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertSame($messageConsumerStub, $context->getFMSConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getFMSMessage());
                $this->assertNull($context->getException());
                $this->assertNull($context->getResult());
                $this->assertFalse($context->isExecutionInterrupted());
                $this->assertSame($queue, $context->getFMSQueue());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind($queue, $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnPreReceivedAndPostReceivedExtensionMethods()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorStub();

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onPreReceived')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertSame($messageConsumerStub, $context->getFMSConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getFMSMessage());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getException());
                $this->assertNull($context->getResult());
                $this->assertFalse($context->isExecutionInterrupted());
            })
        ;
        $extension
            ->expects($this->once())
            ->method('onPostReceived')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertSame($messageConsumerStub, $context->getFMSConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getFMSMessage());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getException());
                $this->assertSame(Result::ACK, $context->getResult());
                $this->assertFalse($context->isExecutionInterrupted());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldAllowInterruptConsumingOnIdle()
    {
        $messageConsumerStub = $this->createMessageConsumerStub($message = null);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onIdle')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) {
                $context->setExecutionInterrupted(true);
            })
        ;
        $extension
            ->expects($this->once())
            ->method('onInterrupted')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertSame($messageConsumerStub, $context->getFMSConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getFMSMessage());
                $this->assertNull($context->getException());
                $this->assertNull($context->getResult());
                $this->assertTrue($context->isExecutionInterrupted());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCloseSessionWhenConsumptionInterrupted()
    {
        $messageConsumerStub = $this->createMessageConsumerStub($message = null);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);
        $contextStub
            ->expects($this->once())
            ->method('close')
        ;

        $messageProcessorMock = $this->createMessageProcessorMock();

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onIdle')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) {
                $context->setExecutionInterrupted(true);
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCloseSessionWhenConsumptionInterruptedByException()
    {
        $expectedException = new \Exception();

        $messageConsumerStub = $this->createMessageConsumerStub($message = $this->createMessageMock());

        $contextStub = $this->createFMSContextStub($messageConsumerStub);
        $contextStub
            ->expects($this->once())
            ->method('close')
        ;

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willThrowException($expectedException)
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        try {
            $queueConsumer->consume();
        } catch (\Exception $e) {
            $this->assertSame($expectedException, $e);
            $this->assertNull($e->getPrevious());

            return;
        }

        $this->fail('Exception throw is expected.');
    }

    public function testShouldSetMainExceptionAsPreviousToExceptionThrownOnInterrupt()
    {
        $mainException = new \Exception();
        $expectedException = new \Exception();

        $messageConsumerStub = $this->createMessageConsumerStub($message = $this->createMessageMock());

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willThrowException($mainException)
        ;

        $extension = $this->createExtension();
        $extension
            ->expects($this->atLeastOnce())
            ->method('onInterrupted')
            ->willThrowException($expectedException)
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        try {
            $queueConsumer->consume();
        } catch (\Exception $e) {
            $this->assertSame($expectedException, $e);
            $this->assertSame($mainException, $e->getPrevious());

            return;
        }

        $this->fail('Exception throw is expected.');
    }

    public function testShouldAllowInterruptConsumingOnPreReceiveButProcessCurrentMessage()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(Result::ACK)
        ;

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onPreReceived')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) {
                $context->setExecutionInterrupted(true);
            })
        ;
        $extension
            ->expects($this->atLeastOnce())
            ->method('onInterrupted')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertSame($messageConsumerStub, $context->getFMSConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getFMSMessage());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getException());
                $this->assertSame(Result::ACK, $context->getResult());
                $this->assertTrue($context->isExecutionInterrupted());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldAllowInterruptConsumingOnPostReceive()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(Result::ACK)
        ;

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onPostReceived')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) {
                $context->setExecutionInterrupted(true);
            })
        ;
        $extension
            ->expects($this->atLeastOnce())
            ->method('onInterrupted')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertSame($messageConsumerStub, $context->getFMSConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getFMSMessage());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getException());
                $this->assertSame(Result::ACK, $context->getResult());
                $this->assertTrue($context->isExecutionInterrupted());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnInterruptedIfExceptionThrow()
    {
        $this->setExpectedException(\Exception::class, 'Process failed');

        $expectedException = new \Exception('Process failed');
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willThrowException($expectedException)
        ;

        $extension = $this->createExtension();
        $extension
            ->expects($this->atLeastOnce())
            ->method('onInterrupted')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $contextStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage,
                $expectedException
            ) {
                $this->assertSame($contextStub, $context->getFMSContext());
                $this->assertSame($messageConsumerStub, $context->getFMSConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getFMSMessage());
                $this->assertSame($expectedException, $context->getException());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getResult());
                $this->assertTrue($context->isExecutionInterrupted());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallExtensionPassedOnRuntime()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(Result::ACK)
        ;

        $runtimeExtension = $this->createExtension();
        $runtimeExtension
            ->expects($this->once())
            ->method('onStart')
            ->with($this->isInstanceOf(Context::class))
        ;
        $runtimeExtension
            ->expects($this->once())
            ->method('onBeforeReceive')
            ->with($this->isInstanceOf(Context::class))
        ;
        $runtimeExtension
            ->expects($this->once())
            ->method('onPreReceived')
            ->with($this->isInstanceOf(Context::class))
        ;
        $runtimeExtension
            ->expects($this->once())
            ->method('onPostReceived')
            ->with($this->isInstanceOf(Context::class))
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume(new ChainExtension([$runtimeExtension]));
    }

    public function testShouldChangeLoggerOnStart()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(Result::ACK)
        ;

        $expectedLogger = new NullLogger();

        $extension = $this->createExtension();
        $extension
            ->expects($this->atLeastOnce())
            ->method('onStart')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($expectedLogger) {
                $context->setLogger($expectedLogger);
            })
        ;
        $extension
            ->expects($this->atLeastOnce())
            ->method('onBeforeReceive')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($expectedLogger) {
                $this->assertSame($expectedLogger, $context->getLogger());
            })
        ;
        $extension
            ->expects($this->atLeastOnce())
            ->method('onPreReceived')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($expectedLogger) {
                $this->assertSame($expectedLogger, $context->getLogger());
            })
        ;

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);

        $queueConsumer = new QueueConsumer($contextStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallEachQueueOneByOne()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $contextStub = $this->createFMSContextStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorStub();
        $anotherMessageProcessorMock = $this->createMessageProcessorStub();

        $queue1 = new NullQueue('aQueueName');
        $queue2 = new NullQueue('aAnotherQueueName');

        $extension = $this->createExtension();
        $extension
            ->expects($this->at(1))
            ->method('onBeforeReceive')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($messageProcessorMock, $queue1) {
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($queue1, $context->getFMSQueue());
            })
        ;
        $extension
            ->expects($this->at(4))
            ->method('onBeforeReceive')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($anotherMessageProcessorMock, $queue2) {
                $this->assertSame($anotherMessageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($queue2, $context->getFMSQueue());
            })
        ;

        $queueConsumer = new QueueConsumer($contextStub, new BreakCycleExtension(2), 0);
        $queueConsumer
            ->bind($queue1, $messageProcessorMock)
            ->bind($queue2, $anotherMessageProcessorMock)
        ;

        $queueConsumer->consume(new ChainExtension([$extension]));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Consumer
     */
    protected function createMessageConsumerStub($message = null)
    {
        $messageConsumerMock = $this->createMock(Consumer::class);
        $messageConsumerMock
            ->expects($this->any())
            ->method('receive')
            ->willReturn($message)
        ;

        return $messageConsumerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FMSContext
     */
    protected function createFMSContextStub($messageConsumer = null)
    {
        $context = $this->createMock(FMSContext::class);
        $context
            ->expects($this->any())
            ->method('createConsumer')
            ->willReturn($messageConsumer)
        ;
        $context
            ->expects($this->any())
            ->method('createQueue')
            ->willReturn($this->createMock(Queue::class))
        ;
        $context
            ->expects($this->any())
            ->method('close')
        ;

        return $context;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageProcessorInterface
     */
    protected function createMessageProcessorMock()
    {
        return $this->createMock(MessageProcessorInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageProcessorInterface
     */
    protected function createMessageProcessorStub()
    {
        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->any())
            ->method('process')
            ->willReturn(Result::ACK)
        ;

        return $messageProcessorMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Message
     */
    protected function createMessageMock()
    {
        return $this->createMock(Message::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtensionInterface
     */
    protected function createExtension()
    {
        return $this->createMock(ExtensionInterface::class);
    }
}
