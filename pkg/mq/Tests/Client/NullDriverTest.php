<?php
namespace Formapro\MessageQueue\Tests\Client;

use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;
use Formapro\MessageQueue\Client\NullDriver;
use Formapro\MessageQueue\Transport\Null\NullContext;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullProducer;
use Formapro\MessageQueue\Transport\Null\NullQueue;

class NullDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithRequiredArguments()
    {
        new NullDriver(new NullContext(), new Config('', '', '', '', ''));
    }

    public function testShouldSendJustCreatedMessageToQueue()
    {
        $config = new Config('', '', '', '', '');
        $queue = new NullQueue('aQueue');

        $transportMessage = new NullMessage();

        $producer = $this->createMessageProducer();
        $producer
            ->expects(self::once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($transportMessage))
        ;

        $session = $this->createContextStub($transportMessage, $producer);

        $driver = new NullDriver($session, $config);

        $driver->send($queue, new Message());
    }

    public function testShouldConvertClientMessageToTransportMessage()
    {
        $config = new Config('', '', '', '', '');

        $message = new Message();
        $message->setBody('theBody');
        $message->setContentType('theContentType');
        $message->setMessageId('theMessageId');
        $message->setTimestamp(12345);
        $message->setDelay(123);
        $message->setExpire(345);
        $message->setPriority(MessagePriority::LOW);
        $message->setHeaders(['theHeaderFoo' => 'theFoo']);
        $message->setProperties(['thePropertyBar' => 'theBar']);

        $transportMessage = new NullMessage();

        $session = $this->createContextStub($transportMessage);

        $driver = new NullDriver($session, $config);

        $transportMessage = $driver->createTransportMessage($message);

        self::assertSame('theBody', $transportMessage->getBody());
        self::assertSame([
            'theHeaderFoo' => 'theFoo',
            'content_type' => 'theContentType',
            'expiration' => 345,
            'delay' => 123,
            'priority' => MessagePriority::LOW,
            'timestamp' => 12345,
            'message_id' => 'theMessageId',
        ], $transportMessage->getHeaders());
        self::assertSame([
            'thePropertyBar' => 'theBar',
        ], $transportMessage->getProperties());
    }

    public function testShouldConvertTransportMessageToClientMessage()
    {
        $config = new Config('', '', '', '', '');

        $message = new NullMessage();
        $message->setBody('theBody');
        $message->setHeaders(['theHeaderFoo' => 'theFoo']);
        $message->setTimestamp(12345);
        $message->setMessageId('theMessageId');
        $message->setHeader('priority', MessagePriority::LOW);
        $message->setHeader('content_type', 'theContentType');
        $message->setHeader('delay', 123);
        $message->setHeader('expiration', 345);
        $message->setProperties(['thePropertyBar' => 'theBar']);

        $driver = new NullDriver($this->createContextStub(), $config);

        $clientMessage = $driver->createClientMessage($message);

        self::assertSame('theBody', $clientMessage->getBody());
        self::assertSame(MessagePriority::LOW, $clientMessage->getPriority());
        self::assertSame('theContentType', $clientMessage->getContentType());
        self::assertSame(123, $clientMessage->getDelay());
        self::assertSame(345, $clientMessage->getExpire());
        self::assertEquals([
            'theHeaderFoo' => 'theFoo',
            'content_type' => 'theContentType',
            'expiration' => 345,
            'delay' => 123,
            'priority' => MessagePriority::LOW,
            'timestamp' => 12345,
            'message_id' => 'theMessageId',
        ], $clientMessage->getHeaders());
        self::assertSame([
            'thePropertyBar' => 'theBar',
        ], $clientMessage->getProperties());
    }

    public function testShouldReturnConfigInstance()
    {
        $config = new Config('', '', '', '', '');

        $driver = new NullDriver($this->createContextStub(), $config);
        $result = $driver->getConfig();

        self::assertSame($config, $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NullContext
     */
    private function createContextStub($message = null, $messageProducer = null)
    {
        $sessionMock = $this->createMock(NullContext::class);
        $sessionMock
            ->expects($this->any())
            ->method('createMessage')
            ->willReturn($message)
        ;
        $sessionMock
            ->expects($this->any())
            ->method('createQueue')
            ->willReturnCallback(function ($name) {
                return new NullQueue($name);
            })
        ;
        $sessionMock
            ->expects($this->any())
            ->method('createProducer')
            ->willReturn($messageProducer)
        ;

        return $sessionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NullProducer
     */
    private function createMessageProducer()
    {
        return $this->createMock(NullProducer::class);
    }
}
