<?php
namespace Formapro\AmqpExt\Tests\Client;

use Formapro\AmqpExt\AmqpContext;
use Formapro\AmqpExt\AmqpMessage;
use Formapro\AmqpExt\AmqpQueue;
use Formapro\AmqpExt\AmqpTopic;
use Formapro\AmqpExt\Client\AmqpDriver;
use Formapro\Fms\InvalidDestinationException;
use Formapro\Fms\Producer;
use Formapro\Fms\Queue;
use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

class AmqpDriverTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementsDriverInterface()
    {
        $this->assertClassImplements(DriverInterface::class, AmqpDriver::class);
    }

    public function testCouldBeConstructedWithRequiredArguments()
    {
        new AmqpDriver($this->createFMSContextMock(), new Config('', '', '', ''));
    }

    public function testShouldReturnConfigObject()
    {
        $config = new Config('', '', '', '');

        $driver = new AmqpDriver($this->createFMSContextMock(), $config);

        $this->assertSame($config, $driver->getConfig());
    }

    public function testShouldCreateAndReturnQueueInstance()
    {
        $expectedQueue = new AmqpQueue('queue-name');

        $context = $this->createFMSContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('name')
            ->will($this->returnValue($expectedQueue))
        ;
        $context
            ->expects($this->once())
            ->method('declareQueue')
            ->with($this->identicalTo($expectedQueue))
        ;

        $driver = new AmqpDriver($context, new Config('', '', '', ''));

        $queue = $driver->createQueue('name');

        $this->assertSame($expectedQueue, $queue);
        $this->assertSame('queue-name', $queue->getQueueName());
        $this->assertSame(['x-max-priority' => 4], $queue->getArguments());
        $this->assertSame(2, $queue->getFlags());
        $this->assertNull($queue->getConsumerTag());
        $this->assertSame([], $queue->getBindArguments());
    }

    public function testShouldConvertTransportMessageToClientMessage()
    {
        $transportMessage = new AmqpMessage();
        $transportMessage->setBody('body');
        $transportMessage->setHeaders(['hkey' => 'hval']);
        $transportMessage->setProperties(['key' => 'val']);
        $transportMessage->setProperty('x-delay', '5678000');
        $transportMessage->setHeader('content_type', 'ContentType');
        $transportMessage->setHeader('expiration', '12345000');
        $transportMessage->setHeader('priority', 3);
        $transportMessage->setMessageId('MessageId');
        $transportMessage->setTimestamp(1000);

        $driver = new AmqpDriver($this->createFMSContextMock(), new Config('', '', '', ''));

        $clientMessage = $driver->createClientMessage($transportMessage);

        $this->assertInstanceOf(Message::class, $clientMessage);
        $this->assertSame('body', $clientMessage->getBody());
        $this->assertSame([
            'hkey' => 'hval',
            'content_type' => 'ContentType',
            'expiration' => '12345000',
            'priority' => 3,
            'message_id' => 'MessageId',
            'timestamp' => 1000,
        ], $clientMessage->getHeaders());
        $this->assertSame([
            'key' => 'val',
            'x-delay' => '5678000',
        ], $clientMessage->getProperties());
        $this->assertSame('MessageId', $clientMessage->getMessageId());
        $this->assertSame(12345, $clientMessage->getExpire());
        $this->assertSame(5678, $clientMessage->getDelay());
        $this->assertSame('ContentType', $clientMessage->getContentType());
        $this->assertSame(1000, $clientMessage->getTimestamp());
        $this->assertSame(3, $clientMessage->getPriority());
    }

    public function testShouldThrowExceptionIfXDelayIsNotNumeric()
    {
        $transportMessage = new AmqpMessage();
        $transportMessage->setProperty('x-delay', 'is-not-numeric');

        $driver = new AmqpDriver($this->createFMSContextMock(), new Config('', '', '', ''));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('x-delay header is not numeric. "is-not-numeric"');

        $driver->createClientMessage($transportMessage);
    }

    public function testShouldThrowExceptionIfExpirationIsNotNumeric()
    {
        $transportMessage = new AmqpMessage();
        $transportMessage->setHeader('expiration', 'is-not-numeric');

        $driver = new AmqpDriver($this->createFMSContextMock(), new Config('', '', '', ''));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('expiration header is not numeric. "is-not-numeric"');

        $driver->createClientMessage($transportMessage);
    }

    public function testShouldThrowExceptionIfCantConvertTransportPriorityToClientPriority()
    {
        $transportMessage = new AmqpMessage();
        $transportMessage->setHeader('priority', 'unknown');

        $driver = new AmqpDriver($this->createFMSContextMock(), new Config('', '', '', ''));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cant convert transport priority to client: "unknown"');

        $driver->createClientMessage($transportMessage);
    }

    public function testShouldThrowExceptionIfCantConvertClientPriorityToTransportPriority()
    {
        $clientMessage = new Message();
        $clientMessage->setPriority('unknown');

        $driver = new AmqpDriver($this->createFMSContextMock(), new Config('', '', '', ''));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Given priority could not be converted to client\'s one. Got: unknown');

        $driver->createTransportMessage($clientMessage);
    }

    public function testShouldConvertClientMessageToTransportMessage()
    {
        $clientMessage = new Message();
        $clientMessage->setBody('body');
        $clientMessage->setHeaders(['hkey' => 'hval']);
        $clientMessage->setProperties(['key' => 'val']);
        $clientMessage->setContentType('ContentType');
        $clientMessage->setExpire(123);
        $clientMessage->setPriority(MessagePriority::VERY_HIGH);
        $clientMessage->setDelay(432);
        $clientMessage->setMessageId('MessageId');
        $clientMessage->setTimestamp(1000);

        $context = $this->createFMSContextMock();
        $context
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn(new AmqpMessage())
        ;

        $driver = new AmqpDriver($context, new Config('', '', '', ''));

        $transportMessage = $driver->createTransportMessage($clientMessage);

        $this->assertInstanceOf(AmqpMessage::class, $transportMessage);
        $this->assertSame('body', $transportMessage->getBody());
        $this->assertSame([
            'hkey' => 'hval',
            'content_type' => 'ContentType',
            'expiration' => '123000',
            'priority' => 4,
            'delivery_mode' => 2,
            'message_id' => 'MessageId',
            'timestamp' => 1000,
        ], $transportMessage->getHeaders());
        $this->assertSame([
            'key' => 'val',
            'x-delay' => '432000',
        ], $transportMessage->getProperties());
        $this->assertSame('MessageId', $transportMessage->getMessageId());
        $this->assertSame(1000, $transportMessage->getTimestamp());
    }

    public function testShouldThrowInvalidDestinationExceptionIfInvalidDestinationInstance()
    {
        $driver = new AmqpDriver($this->createFMSContextMock(), new Config('', '', '', ''));

        $this->expectException(InvalidDestinationException::class);
        $this->expectExceptionMessage('The destination must be an instance of');

        $driver->send($this->createMock(Queue::class), new Message());
    }

    public function testShouldConvertClientMessageToTransportMessageAndSendIt()
    {
        $queue = new AmqpQueue('queue-name');
        $transportMessage = new AmqpMessage();

        $producer = $this->createFMSProducerMock();
        $producer
            ->expects($this->once())
            ->method('send')
            ->with($this->identicalTo($queue), $this->identicalTo($transportMessage))
        ;

        $context = $this->createFMSContextMock();
        $context
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $context
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;

        $message = new Message();

        $driver = new AmqpDriver($context, new Config('', '', '', ''));
        $driver->send($queue, $message);
    }

    public function testShouldCreateDelayQueueOnSendMessageWithDelay()
    {
        $delayTopic = new AmqpTopic('');
        $queue = new AmqpQueue('queue-name');
        $transportMessage = new AmqpMessage();

        $producer = $this->createFMSProducerMock();
        $producer
            ->expects($this->once())
            ->method('send')
            ->with($this->identicalTo($delayTopic), $this->identicalTo($transportMessage))
        ;

        $context = $this->createFMSContextMock();
        $context
            ->expects($this->once())
            ->method('createProducer')
            ->will($this->returnValue($producer))
        ;
        $context
            ->expects($this->once())
            ->method('createMessage')
            ->willReturn($transportMessage)
        ;
        $context
            ->expects($this->once())
            ->method('createTopic')
            ->with('queue-name.delayed')
            ->willReturn($delayTopic)
        ;
        $context
            ->expects($this->once())
            ->method('declareTopic')
            ->with($this->identicalTo($delayTopic))
        ;
        $context
            ->expects($this->once())
            ->method('bind')
            ->with($this->identicalTo($delayTopic), $this->identicalTo($queue))
        ;

        $message = new Message();
        $message->setDelay(123456);

        $driver = new AmqpDriver($context, new Config('', '', '', ''));
        $driver->send($queue, $message);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AmqpContext
     */
    private function createFMSContextMock()
    {
        return $this->createMock(AmqpContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Producer
     */
    private function createFMSProducerMock()
    {
        return $this->createMock(Producer::class);
    }
}
