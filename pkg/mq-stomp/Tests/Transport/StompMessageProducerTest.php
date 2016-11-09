<?php
namespace Formapro\MessageQueueStompTransport\Tests\Transport;

use Formapro\MessageQueue\Transport\Exception\InvalidDestinationException;
use Formapro\MessageQueue\Transport\Exception\InvalidMessageException;
use Formapro\MessageQueue\Transport\MessageProducerInterface;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueueStompTransport\Test\ClassExtensionTrait;
use Formapro\MessageQueueStompTransport\Transport\StompDestination;
use Formapro\MessageQueueStompTransport\Transport\StompMessage;
use Formapro\MessageQueueStompTransport\Transport\StompMessageProducer;
use Stomp\Client;
use Stomp\Transport\Message;

class StompMessageProducerTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProducerInterface()
    {
        $this->assertClassImplements(MessageProducerInterface::class, StompMessageProducer::class);
    }

    public function testShouldThrowInvalidDestinationExceptionWhenDestinationIsWrongType()
    {
        $this->expectException(InvalidDestinationException::class);
        $this->expectExceptionMessage('The destination must be an instance of');

        $producer = new StompMessageProducer($this->createStompClientMock());

        $producer->send(new NullQueue(''), new StompMessage());
    }

    public function testShouldThrowInvalidMessageExceptionWhenMessageIsWrongType()
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('The message must be an instance of');

        $producer = new StompMessageProducer($this->createStompClientMock());

        $producer->send(new StompDestination(''), new NullMessage());
    }

    public function testShouldSendMessage()
    {
        $client = $this->createStompClientMock();
        $client
            ->expects($this->once())
            ->method('send')
            ->with('/queue/name', $this->isInstanceOf(Message::class))
        ;

        $producer = new StompMessageProducer($client);

        $producer->send(new StompDestination('name'), new StompMessage('body'));
    }

    public function testShouldEncodeMessageHeadersAndProperties()
    {
        $stompMessage = null;
        $client = $this->createStompClientMock();
        $client
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($destination, Message $message) use (&$stompMessage) {
                $stompMessage = $message;
            })
        ;

        $producer = new StompMessageProducer($client);

        $message = new StompMessage('', ['key' => 'value'], ['hkey' => false]);

        $producer->send(new StompDestination('name'), $message);

        $expectedHeaders = [
            'hkey' => 'false',
            '__property_key' => 's:value',
            'durable' => 'false',
            'auto-delete' => 'true',
            'exclusive' => 'false',
        ];

        $this->assertEquals($expectedHeaders, $stompMessage->getHeaders());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Client
     */
    private function createStompClientMock()
    {
        return $this->createMock(Client::class);
    }
}
