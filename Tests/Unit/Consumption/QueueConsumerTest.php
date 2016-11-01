<?php
namespace FormaPro\MessageQueue\Tests\Unit\Consumption;

use FormaPro\MessageQueue\Consumption\Context;
use FormaPro\MessageQueue\Consumption\ExtensionInterface;
use FormaPro\MessageQueue\Consumption\ChainExtension;
use FormaPro\MessageQueue\Consumption\MessageProcessorInterface;
use FormaPro\MessageQueue\Consumption\QueueConsumer;
use FormaPro\MessageQueue\Tests\Unit\Consumption\Mock\BreakCycleExtension;
use FormaPro\MessageQueue\Transport\ConnectionInterface;
use FormaPro\MessageQueue\Transport\MessageInterface;
use FormaPro\MessageQueue\Transport\MessageConsumerInterface;
use FormaPro\MessageQueue\Transport\Null\NullQueue;
use FormaPro\MessageQueue\Transport\QueueInterface;
use FormaPro\MessageQueue\Transport\SessionInterface;
use Psr\Log\NullLogger;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class QueueConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithConnectionAndExtensionsAsArguments()
    {
        new QueueConsumer($this->createConnectionStub(), null, 0);
    }

    public function testCouldBeConstructedWithConnectionOnly()
    {
        new QueueConsumer($this->createConnectionStub());
    }

    public function testCouldBeConstructedWithConnectionAndSingleExtension()
    {
        new QueueConsumer($this->createConnectionStub(), $this->createExtension());
    }

    public function testShouldSetEmptyArrayToBoundMessageProcessorsPropertyInConstructor()
    {
        $consumer = new QueueConsumer($this->createConnectionStub(), null, 0);

        $this->assertAttributeSame([], 'boundMessageProcessors', $consumer);
    }

    public function testShouldAllowGetConnectionSetInConstructor()
    {
        $expectedConnection = $this->createConnectionStub();

        $consumer = new QueueConsumer($expectedConnection, null, 0);

        $this->assertSame($expectedConnection, $consumer->getConnection());
    }

    public function testThrowIfQueueNameEmptyOnBind()
    {
        $messageProcessorMock = $this->createMessageProcessorMock();

        $consumer = new QueueConsumer($this->createConnectionStub(), null, 0);

        $this->setExpectedException(\LogicException::class, 'The queue name must be not empty.');
        $consumer->bind(new NullQueue(''), $messageProcessorMock);
    }

    public function testThrowIfQueueAlreadyBoundToMessageProcessorOnBind()
    {
        $messageProcessorMock = $this->createMessageProcessorMock();

        $consumer = new QueueConsumer($this->createConnectionStub(), null, 0);

        $consumer->bind(new NullQueue('theQueueName'), $messageProcessorMock);

        $this->setExpectedException(\LogicException::class, 'The queue was already bound.');
        $consumer->bind(new NullQueue('theQueueName'), $messageProcessorMock);
    }

    public function testShouldAllowBindMessageProcessorToQueue()
    {
        $queue = new NullQueue('theQueueName');
        $messageProcessorMock = $this->createMessageProcessorMock();

        $consumer = new QueueConsumer($this->createConnectionStub(), null, 0);

        $consumer->bind($queue, $messageProcessorMock);

        $this->assertAttributeSame(['theQueueName' => [$queue, $messageProcessorMock]], 'boundMessageProcessors', $consumer);
    }

    public function testShouldReturnSelfOnBind()
    {
        $messageProcessorMock = $this->createMessageProcessorMock();

        $consumer = new QueueConsumer($this->createConnectionStub(), null, 0);

        $this->assertSame($consumer, $consumer->bind(new NullQueue('aQueueName'), $messageProcessorMock));
    }

    public function testShouldSubscribeToGivenQueueAndQuitAfterFifthIdleCycle()
    {
        $expectedQueue = new NullQueue('theQueueName');

        $messageConsumerMock = $this->createMock(MessageConsumerInterface::class);
        $messageConsumerMock
            ->expects($this->exactly(5))
            ->method('receive')
            ->willReturn(null)
        ;

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->once())
            ->method('createConsumer')
            ->with($this->identicalTo($expectedQueue))
            ->willReturn($messageConsumerMock)
        ;

        $connectionStub = $this->createConnectionStub($sessionMock);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->never())
            ->method('process')
        ;

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(5), 0);
        $queueConsumer->bind($expectedQueue, $messageProcessorMock);
        $queueConsumer->consume();
    }

    public function testShouldProcessFiveMessagesAndQuit()
    {
        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->exactly(5))
            ->method('process')
            ->willReturn(MessageProcessorInterface::ACK)
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(5), 0);
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

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn(MessageProcessorInterface::ACK)
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testThrowIfMessageProcessorReturnNull()
    {
        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn(null)
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(1), 0);
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

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn(MessageProcessorInterface::REJECT)
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(1), 0);
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

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn(MessageProcessorInterface::REQUEUE)
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testThrowIfMessageProcessorReturnInvalidStatus()
    {
        $this->setExpectedException(\LogicException::class, 'Status is not supported: invalidStatus');

        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($messageMock))
            ->willReturn('invalidStatus')
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(1), 0);
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
                $context->setStatus(MessageProcessorInterface::ACK);
            })
        ;

        $messageMock = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($messageMock);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->never())
            ->method('process')
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnStartExtensionMethod()
    {
        $messageConsumerStub = $this->createMessageConsumerStub($message = null);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onStart')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertNull($context->getMessageConsumer());
                $this->assertNull($context->getMessageProcessor());
                $this->assertNull($context->getLogger());
                $this->assertNull($context->getMessage());
                $this->assertNull($context->getException());
                $this->assertNull($context->getStatus());
                $this->assertNull($context->getQueue());
                $this->assertFalse($context->isExecutionInterrupted());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnIdleExtensionMethod()
    {
        $messageConsumerStub = $this->createMessageConsumerStub($message = null);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onIdle')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertSame($messageConsumerStub, $context->getMessageConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getMessage());
                $this->assertNull($context->getException());
                $this->assertNull($context->getStatus());
                $this->assertFalse($context->isExecutionInterrupted());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnBeforeReceiveExtensionMethod()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorStub();

        $queue = new NullQueue('aQueueName');

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onBeforeReceive')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage,
                $queue
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertSame($messageConsumerStub, $context->getMessageConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getMessage());
                $this->assertNull($context->getException());
                $this->assertNull($context->getStatus());
                $this->assertFalse($context->isExecutionInterrupted());
                $this->assertSame($queue, $context->getQueue());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind($queue, $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnPreReceivedAndPostReceivedExtensionMethods()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorStub();

        $extension = $this->createExtension();
        $extension
            ->expects($this->once())
            ->method('onPreReceived')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertSame($messageConsumerStub, $context->getMessageConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getMessage());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getException());
                $this->assertNull($context->getStatus());
                $this->assertFalse($context->isExecutionInterrupted());
            })
        ;
        $extension
            ->expects($this->once())
            ->method('onPostReceived')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use (
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertSame($messageConsumerStub, $context->getMessageConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getMessage());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getException());
                $this->assertSame(MessageProcessorInterface::ACK, $context->getStatus());
                $this->assertFalse($context->isExecutionInterrupted());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldAllowInterruptConsumingOnIdle()
    {
        $messageConsumerStub = $this->createMessageConsumerStub($message = null);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

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
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertSame($messageConsumerStub, $context->getMessageConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getMessage());
                $this->assertNull($context->getException());
                $this->assertNull($context->getStatus());
                $this->assertTrue($context->isExecutionInterrupted());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCloseSessionWhenConsumptionInterrupted()
    {
        $messageConsumerStub = $this->createMessageConsumerStub($message = null);

        $sessionMock = $this->createSessionStub($messageConsumerStub);
        $sessionMock
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

        $connectionStub = $this->createConnectionStub($sessionMock);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCloseSessionWhenConsumptionInterruptedByException()
    {
        $expectedException = new \Exception;

        $messageConsumerStub = $this->createMessageConsumerStub($message = $this->createMessageMock());

        $sessionMock = $this->createSessionStub($messageConsumerStub);
        $sessionMock
            ->expects($this->once())
            ->method('close')
        ;

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willThrowException($expectedException)
        ;

        $connectionStub = $this->createConnectionStub($sessionMock);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(1), 0);
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
        $mainException = new \Exception;
        $expectedException = new \Exception;

        $messageConsumerStub = $this->createMessageConsumerStub($message = $this->createMessageMock());

        $sessionMock = $this->createSessionStub($messageConsumerStub);

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

        $connectionStub = $this->createConnectionStub($sessionMock);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
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

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(MessageProcessorInterface::ACK)
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
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertSame($messageConsumerStub, $context->getMessageConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getMessage());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getException());
                $this->assertSame(MessageProcessorInterface::ACK, $context->getStatus());
                $this->assertTrue($context->isExecutionInterrupted());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldAllowInterruptConsumingOnPostReceive()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(MessageProcessorInterface::ACK)
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
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertSame($messageConsumerStub, $context->getMessageConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getMessage());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getException());
                $this->assertSame(MessageProcessorInterface::ACK, $context->getStatus());
                $this->assertTrue($context->isExecutionInterrupted());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallOnInterruptedIfExceptionThrow()
    {
        $this->setExpectedException(\Exception::class, 'Process failed');

        $expectedException = new \Exception('Process failed');
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

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
                $sessionStub,
                $messageConsumerStub,
                $messageProcessorMock,
                $expectedMessage,
                $expectedException
            ) {
                $this->assertSame($sessionStub, $context->getSession());
                $this->assertSame($messageConsumerStub, $context->getMessageConsumer());
                $this->assertSame($messageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($expectedMessage, $context->getMessage());
                $this->assertSame($expectedException, $context->getException());
                $this->assertInstanceOf(NullLogger::class, $context->getLogger());
                $this->assertNull($context->getStatus());
                $this->assertTrue($context->isExecutionInterrupted());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);
        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallExtensionPassedOnRuntime()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(MessageProcessorInterface::ACK)
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

        $connectionStub = $this->createConnectionStub($sessionStub);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(1), 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume(new ChainExtension([$runtimeExtension]));
    }

    public function testShouldChangeLoggerOnStart()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

        $messageProcessorMock = $this->createMessageProcessorMock();
        $messageProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(MessageProcessorInterface::ACK)
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

        $connectionStub = $this->createConnectionStub($sessionStub);

        $chainExtensions = new ChainExtension([$extension, new BreakCycleExtension(1)]);

        $queueConsumer = new QueueConsumer($connectionStub, $chainExtensions, 0);
        $queueConsumer->bind(new NullQueue('aQueueName'), $messageProcessorMock);

        $queueConsumer->consume();
    }

    public function testShouldCallEachQueueOneByOne()
    {
        $expectedMessage = $this->createMessageMock();
        $messageConsumerStub = $this->createMessageConsumerStub($expectedMessage);

        $sessionStub = $this->createSessionStub($messageConsumerStub);

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
                $this->assertSame($queue1, $context->getQueue());
            })
        ;
        $extension
            ->expects($this->at(4))
            ->method('onBeforeReceive')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($anotherMessageProcessorMock, $queue2) {
                $this->assertSame($anotherMessageProcessorMock, $context->getMessageProcessor());
                $this->assertSame($queue2, $context->getQueue());
            })
        ;

        $connectionStub = $this->createConnectionStub($sessionStub);

        $queueConsumer = new QueueConsumer($connectionStub, new BreakCycleExtension(2), 0);
        $queueConsumer
            ->bind($queue1, $messageProcessorMock)
            ->bind($queue2, $anotherMessageProcessorMock)
        ;

        $queueConsumer->consume(new ChainExtension([$extension]));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageConsumerInterface
     */
    protected function createMessageConsumerStub($message = null)
    {
        $messageConsumerMock = $this->createMock(MessageConsumerInterface::class);
        $messageConsumerMock
            ->expects($this->any())
            ->method('receive')
            ->willReturn($message)
        ;

        return $messageConsumerMock;
    }

    /**
     * @return ConnectionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createConnectionStub($session = null)
    {
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionMock
            ->expects($this->any())
            ->method('createSession')
            ->willReturn($session)
        ;

        return $connectionMock;
    }

    /**
     * @return SessionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createSessionStub($messageConsumer = null)
    {
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock
            ->expects($this->any())
            ->method('createConsumer')
            ->willReturn($messageConsumer)
        ;
        $sessionMock
            ->expects($this->any())
            ->method('createQueue')
            ->willReturn($this->createMock(QueueInterface::class))
        ;
        $sessionMock
            ->expects($this->any())
            ->method('close')
        ;

        return $sessionMock;
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
            ->willReturn(MessageProcessorInterface::ACK)
        ;

        return $messageProcessorMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageInterface
     */
    protected function createMessageMock()
    {
        return $this->createMock(MessageInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtensionInterface
     */
    protected function createExtension()
    {
        return $this->createMock(ExtensionInterface::class);
    }
}
