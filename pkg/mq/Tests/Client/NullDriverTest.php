<?php
namespace Formapro\MessageQueue\Tests\Client;

use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;
use Formapro\MessageQueue\Client\NullDriver;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullProducer;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueue\Transport\Null\NullContext;

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

        $session = $this->createSessionStub($transportMessage, $producer);

        $driver = new NullDriver($session, $config);

        $driver->send($queue, new Message());
    }

    public function testShouldConvertClientMessageToTransportMessage()
    {
        $config = new Config('', '', '', '', '');
        $queue = new NullQueue('aQueue');

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

        $producer = $this->createMessageProducer();
        $producer
            ->expects(self::once())
            ->method('send')
        ;

        $session = $this->createSessionStub($transportMessage, $producer);

        $driver = new NullDriver($session, $config);

        $driver->send($queue, $message);

        self::assertSame('theBody', $transportMessage->getBody());
        self::assertSame([
            'theHeaderFoo' => 'theFoo',
            'content_type' => 'theContentType',
            'expiration' => 345,
            'delay' => 123,
            'priority' => MessagePriority::LOW,
        ], $transportMessage->getHeaders());
        self::assertSame([
            'thePropertyBar' => 'theBar',
        ], $transportMessage->getProperties());
    }

    public function testShouldReturnConfigInstance()
    {
        $config = new Config('', '', '', '', '');

        $driver = new NullDriver($this->createSessionStub(), $config);
        $result = $driver->getConfig();

        self::assertSame($config, $result);
    }

    public function testAllowCreateTransportMessage()
    {
        $config = new Config('', '', '', '', '');

        $message = new NullMessage();

        $session = $this->createSessionMock();
        $session
            ->expects(self::once())
            ->method('createMessage')
            ->willReturn($message)
        ;

        $driver = new NullDriver($session, $config);

        self::assertSame($message, $driver->createTransportMessage());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NullContext
     */
    private function createSessionMock()
    {
        return $this->createMock(NullContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NullContext
     */
    private function createSessionStub($message = null, $messageProducer = null)
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
