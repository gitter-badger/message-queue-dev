<?php
namespace Formapro\MessageQueueStompTransport\Tests\Transport;

use Formapro\MessageQueue\Transport\Exception\InvalidDestinationException;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueue\Transport\SessionInterface;
use Formapro\MessageQueueStompTransport\Test\ClassExtensionTrait;
use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompDestination;
use Formapro\MessageQueueStompTransport\Transport\StompMessage;
use Formapro\MessageQueueStompTransport\Transport\StompMessageConsumer;
use Formapro\MessageQueueStompTransport\Transport\StompMessageProducer;
use Formapro\MessageQueueStompTransport\Transport\StompSession;

class StompSessionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementSessionInterface()
    {
        $this->assertClassImplements(SessionInterface::class, StompSession::class);
    }

    public function testCouldBeCreatedWithRequiredArguments()
    {
        new StompSession($this->createStompClientMock());
    }

    public function testShouldCreateMessageInstance()
    {
        $session = new StompSession($this->createStompClientMock());

        $message = $session->createMessage('the body', ['key' => 'value'], ['hkey' => 'hvalue']);

        $this->assertInstanceOf(StompMessage::class, $message);
        $this->assertSame('the body', $message->getBody());
        $this->assertSame(['hkey' => 'hvalue'], $message->getHeaders());
        $this->assertSame(['key' => 'value'], $message->getProperties());
    }

    public function testShouldCreateQueueInstance()
    {
        $session = new StompSession($this->createStompClientMock());

        $queue = $session->createQueue('the name');

        $this->assertInstanceOf(StompDestination::class, $queue);
        $this->assertSame('the name', $queue->getQueueName());
        $this->assertSame('the name', $queue->getTopicName());
        $this->assertSame(StompDestination::TYPE_QUEUE, $queue->getType());
    }

    public function testShouldCreateTopicInstance()
    {
        $session = new StompSession($this->createStompClientMock());

        $topic = $session->createTopic('the name');

        $this->assertInstanceOf(StompDestination::class, $topic);
        $this->assertSame('the name', $topic->getQueueName());
        $this->assertSame('the name', $topic->getTopicName());
        $this->assertSame(StompDestination::TYPE_EXCHANGE, $topic->getType());
    }

    public function testThrowInvalidDestinationException()
    {
        $this->expectException(InvalidDestinationException::class);
        $this->expectExceptionMessage('The destination must be an instance of');

        $session = new StompSession($this->createStompClientMock());
        $session->createConsumer(new NullQueue(''));
    }

    public function testShouldCreateMessageConsumerInstance()
    {
        $session = new StompSession($this->createStompClientMock());

        $this->assertInstanceOf(StompMessageConsumer::class, $session->createConsumer(new StompDestination('')));
    }

    public function testShouldCreateMessageProducerInstance()
    {
        $session = new StompSession($this->createStompClientMock());

        $this->assertInstanceOf(StompMessageProducer::class, $session->createProducer());
    }

    public function testShouldCloseConnections()
    {
        $client = $this->createStompClientMock();
        $client
            ->expects($this->once())
            ->method('disconnect')
        ;

        $session = new StompSession($client);

        $session->createProducer();
        $session->createConsumer(new StompDestination(''));

        $session->close();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|BufferedStompClient
     */
    private function createStompClientMock()
    {
        return $this->createMock(BufferedStompClient::class);
    }
}
