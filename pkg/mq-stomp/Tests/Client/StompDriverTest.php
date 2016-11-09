<?php
namespace Formapro\MessageQueueStompTransport\Tests\Client;

use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;
use Formapro\MessageQueue\Transport\Exception\InvalidDestinationException;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueueStompTransport\Client\StompDriver;
use Formapro\MessageQueueStompTransport\Test\ClassExtensionTrait;
use Formapro\MessageQueueStompTransport\Transport\StompDestination;
use Formapro\MessageQueueStompTransport\Transport\StompMessage;
use Formapro\MessageQueueStompTransport\Transport\StompMessageProducer;
use Formapro\MessageQueueStompTransport\Transport\StompSession;

class StompDriverTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementsDriverInterface()
    {
        $this->assertClassImplements(DriverInterface::class, StompDriver::class);
    }

    public function testCouldBeConstructedWithRequiredArguments()
    {
        new StompDriver($this->createSessionMock(), new Config('', '', '', ''));
    }

    public function testShouldReturnConfigObject()
    {
        $config = new Config('', '', '', '');

        $driver = new StompDriver($this->createSessionMock(), $config);

        $this->assertSame($config, $driver->getConfig());
    }

    public function testShouldCreateAndReturnQueueInstance()
    {
        $expectedQueue = new StompDestination('');

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createQueue')
            ->with('name')
            ->will($this->returnValue($expectedQueue))
        ;

        $driver = new StompDriver($session, new Config('', '', '', ''));

        $queue = $driver->createQueue('name');

        $expectedHeaders = [
            'durable' => true,
            'auto-delete' => false,
            'exclusive' => false,
            'x-max-priority' => 4,
        ];

        $this->assertSame($expectedQueue, $queue);
        $this->assertTrue($queue->isDurable());
        $this->assertFalse($queue->isAutoDelete());
        $this->assertFalse($queue->isExclusive());
        $this->assertSame($expectedHeaders, $queue->getHeaders());
    }

    public function testShouldCreateAndReturnMessageInstance()
    {
        $message = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue($message))
        ;

        $driver = new StompDriver($session, new Config('', '', '', ''));

        $this->assertSame($message, $driver->createTransportMessage());
    }

    public function testShouldThrowInvalidDestinationExceptionIfInvalidDestinationInstance()
    {
        $driver = new StompDriver($this->createSessionMock(), new Config('', '', '', ''));

        $this->expectException(InvalidDestinationException::class);
        $this->expectExceptionMessage('The destination must be an instance of');

        $driver->send(new NullQueue(''), new Message());
    }

    public function testShouldSetContentTypeHeader()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $queue = new StompDestination('');
        $message = new Message();
        $message->setContentType('the-content-type');

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertSame('the-content-type', $transportMessage->getHeader('content-type'));
    }

    public function testShouldSetExpirationHeader()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $queue = new StompDestination('');
        $message = new Message();
        $message->setExpire(123);

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertSame('123000', $transportMessage->getHeader('expiration'));
    }

    public function testShouldSetPriorityHeader()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $queue = new StompDestination('');
        $message = new Message();
        $message->setPriority(MessagePriority::VERY_HIGH);

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertSame(4, $transportMessage->getHeader('priority'));
    }

    public function testShouldThrowExceptionIfCantConvertClientPriorityToTransportPriority()
    {
        $session = $this->createSessionMock();

        $queue = new StompDestination('');
        $message = new Message();
        $message->setPriority('unknown-priority');

        $driver = new StompDriver($session, new Config('', '', '', ''));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cant convert client priority to transport: "unknown-priority"');

        $driver->send($queue, $message);
    }

    public function testShouldSetDelayHeader()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $delayTopic = new StompDestination('');

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;
        $session
            ->expects($this->once())
            ->method('createTopic')
            ->with('destination.delayed')
            ->willReturn($delayTopic)
        ;

        $queue = new StompDestination('destination');
        $message = new Message();
        $message->setDelay(123);

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertSame('123000', $transportMessage->getHeader('x-delay'));
    }

    public function testShouldSetMessageIdHeader()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $queue = new StompDestination('');
        $message = new Message();
        $message->setMessageId('message-id');

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertSame('message-id', $transportMessage->getHeader('message_id'));
    }

    public function testShouldSetTimestampHeader()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $queue = new StompDestination('');
        $message = new Message();
        $message->setTimestamp(123);

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertSame('123', $transportMessage->getHeader('timestamp'));
    }

    public function testShouldSetProperties()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $queue = new StompDestination('');
        $message = new Message();
        $message->setProperties(['key' => 'value']);

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertSame(['key' => 'value'], $transportMessage->getProperties());
    }

    public function testShouldSetBody()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $queue = new StompDestination('');
        $message = new Message();
        $message->setBody('body');

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertSame('body', $transportMessage->getBody());
    }

    public function testShouldSetMessagePersistent()
    {
        $producer = $this->createProducerMock();

        $transportMessage = new StompMessage();

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $queue = new StompDestination('');
        $message = new Message();

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);

        $this->assertTrue($transportMessage->isPersistent());
    }

    public function testShouldSendTransportMessage()
    {
        $queue = new StompDestination('');
        $transportMessage = new StompMessage();

        $producer = $this->createProducerMock();
        $producer
            ->expects($this->once())
            ->method('send')
            ->with($this->identicalTo($queue), $this->identicalTo($transportMessage))
        ;

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $session
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $message = new Message();

        $driver = new StompDriver($session, new Config('', '', '', ''));
        $driver->send($queue, $message);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StompSession
     */
    private function createSessionMock()
    {
        return $this->createMock(StompSession::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StompMessageProducer
     */
    private function createProducerMock()
    {
        return $this->createMock(StompMessageProducer::class);
    }
}