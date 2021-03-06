<?php
namespace Formapro\MessageQueue\Tests\Client\ConsumptionExtension;

use Formapro\Fms\Context as FMSContext;
use Formapro\Fms\Queue;
use Formapro\MessageQueue\Client\ConsumptionExtension\DelayRedeliveredMessageExtension;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DelayRedeliveredMessageExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithRequiredArguments()
    {
        new DelayRedeliveredMessageExtension($this->createDriverMock(), 12345);
    }

    public function testShouldSendDelayedMessageAndRejectOriginalMessage()
    {
        $queue = new NullQueue('queue');

        $originMessage = new NullMessage();
        $originMessage->setRedelivered(true);
        $originMessage->setBody('theBody');
        $originMessage->setHeaders(['foo' => 'fooVal']);
        $originMessage->setProperties(['bar' => 'barVal']);

        /** @var Message $delayedMessage */
        $delayedMessage = null;

        $driver = $this->createDriverMock();
        $driver
            ->expects(self::once())
            ->method('send')
            ->with($this->identicalTo($queue), $this->isInstanceOf(Message::class))
            ->willReturnCallback(function ($queue, $message) use (&$delayedMessage) {
                $delayedMessage = $message;
            })
        ;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->at(0))
            ->method('debug')
            ->with('[DelayRedeliveredMessageExtension] Send delayed message')
        ;
        $logger
            ->expects($this->at(1))
            ->method('debug')
            ->with(
                '[DelayRedeliveredMessageExtension] '.
                'Reject redelivered original message by setting reject status to context.'
            )
        ;

        $context = new Context($this->createFMSContextMock());
        $context->setFMSQueue($queue);
        $context->setFMSMessage($originMessage);
        $context->setLogger($logger);

        self::assertNull($context->getResult());

        $extension = new DelayRedeliveredMessageExtension($driver, 12345);
        $extension->onPreReceived($context);

        self::assertEquals(Result::REJECT, $context->getResult());

        self::assertInstanceOf(Message::class, $delayedMessage);
        self::assertEquals('theBody', $delayedMessage->getBody());
        self::assertEquals(['foo' => 'fooVal'], $delayedMessage->getHeaders());
        self::assertEquals([
            'bar' => 'barVal',
            'fp-redeliver-count' => 1,
        ], $delayedMessage->getProperties());
    }

    public function testShouldDoNothingIfMessageIsNotRedelivered()
    {
        $message = new NullMessage();

        $driver = $this->createDriverMock();
        $driver
            ->expects(self::never())
            ->method('send')
        ;

        $context = new Context($this->createFMSContextMock());
        $context->setFMSMessage($message);

        $extension = new DelayRedeliveredMessageExtension($driver, 12345);
        $extension->onPreReceived($context);

        self::assertNull($context->getResult());
    }

    public function testShouldAddRedeliverCountHeaderAndRemoveItAfterDelayFromOriginalMessage()
    {
        $queue = new NullQueue('queue');

        $message = new NullMessage();
        $message->setRedelivered(true);

        $driver = $this->createDriverMock();
        $driver
            ->expects(self::once())
            ->method('send')
            ->with($this->identicalTo($queue), $this->isInstanceOf(Message::class))
            ->will($this->returnCallback(function (Queue $queue, Message $message) {
                $properties = $message->getProperties();
                self::assertArrayHasKey(DelayRedeliveredMessageExtension::PROPERTY_REDELIVER_COUNT, $properties);
                self::assertSame(1, $properties[DelayRedeliveredMessageExtension::PROPERTY_REDELIVER_COUNT]);
            }))
        ;

        $context = new Context($this->createFMSContextMock());
        $context->setFMSQueue($queue);
        $context->setFMSMessage($message);
        $context->setLogger(new NullLogger());

        self::assertNull($context->getResult());

        $extension = new DelayRedeliveredMessageExtension($driver, 12345);
        $extension->onPreReceived($context);

        self::assertEquals(Result::REJECT, $context->getResult());
        self::assertArrayNotHasKey(
            DelayRedeliveredMessageExtension::PROPERTY_REDELIVER_COUNT,
            $message->getProperties()
        );
    }

    public function testShouldIncrementRedeliverCountHeaderAndSetOriginalCountAfterDelay()
    {
        $queue = new NullQueue('queue');

        $message = new NullMessage();
        $message->setRedelivered(true);
        $message->setProperties([
            DelayRedeliveredMessageExtension::PROPERTY_REDELIVER_COUNT => 7,
        ]);

        $driver = $this->createDriverMock();
        $driver
            ->expects(self::once())
            ->method('send')
            ->with($this->identicalTo($queue), $this->isInstanceOf(Message::class))
            ->will($this->returnCallback(function (Queue $queue, Message $message) {
                $properties = $message->getProperties();
                self::assertArrayHasKey(DelayRedeliveredMessageExtension::PROPERTY_REDELIVER_COUNT, $properties);
                self::assertSame(8, $properties[DelayRedeliveredMessageExtension::PROPERTY_REDELIVER_COUNT]);
            }))
        ;

        $context = new Context($this->createFMSContextMock());
        $context->setFMSQueue($queue);
        $context->setFMSMessage($message);
        $context->setLogger(new NullLogger());

        self::assertNull($context->getResult());

        $extension = new DelayRedeliveredMessageExtension($driver, 12345);
        $extension->onPreReceived($context);

        self::assertEquals(Result::REJECT, $context->getResult());
        $properties = $message->getProperties();
        self::assertArrayHasKey(DelayRedeliveredMessageExtension::PROPERTY_REDELIVER_COUNT, $properties);
        self::assertSame(7, $properties[DelayRedeliveredMessageExtension::PROPERTY_REDELIVER_COUNT]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DriverInterface
     */
    private function createDriverMock()
    {
        return $this->createMock(DriverInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FMSContext
     */
    private function createFMSContextMock()
    {
        return $this->createMock(FMSContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }
}
