<?php
namespace Formapro\Stomp\Tests\Transport;

use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\Exception\InvalidMessageException;
use Formapro\Jms\Message as JmsMessage;
use Formapro\Jms\JMSProducer;
use Formapro\Jms\Queue;
use Formapro\Stomp\Test\ClassExtensionTrait;
use Formapro\Stomp\Transport\StompDestination;
use Formapro\Stomp\Transport\StompMessage;
use Formapro\Stomp\Transport\StompProducer;
use Stomp\Client;
use Stomp\Transport\Message;

class StompProducerTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProducerInterface()
    {
        $this->assertClassImplements(JMSProducer::class, StompProducer::class);
    }

    public function testShouldThrowInvalidDestinationExceptionWhenDestinationIsWrongType()
    {
        $this->expectException(InvalidDestinationException::class);
        $this->expectExceptionMessage('The destination must be an instance of');

        $producer = new StompProducer($this->createStompClientMock());

        $producer->send($this->createMock(Queue::class), new StompMessage());
    }

    public function testShouldThrowInvalidMessageExceptionWhenMessageIsWrongType()
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('The message must be an instance of');

        $producer = new StompProducer($this->createStompClientMock());

        $producer->send(new StompDestination(''), $this->createMock(JmsMessage::class));
    }

    public function testShouldSendMessage()
    {
        $client = $this->createStompClientMock();
        $client
            ->expects($this->once())
            ->method('send')
            ->with('/queue/name', $this->isInstanceOf(Message::class))
        ;

        $producer = new StompProducer($client);

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

        $producer = new StompProducer($client);

        $message = new StompMessage('', ['key' => 'value'], ['hkey' => false]);

        $producer->send(new StompDestination('name'), $message);

        $expectedHeaders = [
            'hkey' => 'false',
            'durable' => 'false',
            'auto-delete' => 'true',
            'exclusive' => 'false',
            '_type_hkey' => 'b',
            '_type_durable' => 'b',
            '_type_auto-delete' => 'b',
            '_type_exclusive' => 'b',
            '_property_key' => 'value',
            '_property__type_key' => 's',
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
