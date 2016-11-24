<?php
namespace Formapro\AmqpExt\Tests\Client;

use Formapro\AmqpExt\AmqpContext;
use Formapro\AmqpExt\AmqpMessage;
use Formapro\AmqpExt\Client\AmqpDriver;
use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;

class AmqpDriverTest extends \PHPUnit_Framework_TestCase
{
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

        $driver = new AmqpDriver($this->createContextMock(), new Config('', '', '', ''));

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

        $driver = new AmqpDriver($this->createContextMock(), new Config('', '', '', ''));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('x-delay header is not numeric. "is-not-numeric"');

        $driver->createClientMessage($transportMessage);
    }

    public function testShouldThrowExceptionIfExpirationIsNotNumeric()
    {
        $transportMessage = new AmqpMessage();
        $transportMessage->setHeader('expiration', 'is-not-numeric');

        $driver = new AmqpDriver($this->createContextMock(), new Config('', '', '', ''));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('expiration header is not numeric. "is-not-numeric"');

        $driver->createClientMessage($transportMessage);
    }

    public function testShouldThrowExceptionIfCantConvertTransportPriorityToClientPriority()
    {
        $transportMessage = new AmqpMessage();
        $transportMessage->setHeader('priority', 'unknown');

        $driver = new AmqpDriver($this->createContextMock(), new Config('', '', '', ''));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cant convert transport priority to client: "unknown"');

        $driver->createClientMessage($transportMessage);
    }

    public function testShouldThrowExceptionIfCantConvertClientPriorityToTransportPriority()
    {
        $clientMessage = new Message();
        $clientMessage->setPriority('unknown');

        $driver = new AmqpDriver($this->createContextMock(), new Config('', '', '', ''));

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

        $context = $this->createContextMock();
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AmqpContext
     */
    private function createContextMock()
    {
        return $this->createMock(AmqpContext::class);
    }
}
