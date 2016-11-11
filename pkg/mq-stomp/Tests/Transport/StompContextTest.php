<?php
namespace Formapro\MessageQueueStompTransport\Tests\Transport;

use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\JMSContext;
use Formapro\Jms\Queue;
use Formapro\MessageQueueStompTransport\Test\ClassExtensionTrait;
use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompContext;
use Formapro\MessageQueueStompTransport\Transport\StompDestination;
use Formapro\MessageQueueStompTransport\Transport\StompMessage;
use Formapro\MessageQueueStompTransport\Transport\StompConsumer;
use Formapro\MessageQueueStompTransport\Transport\StompProducer;

class StompContextTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementSessionInterface()
    {
        $this->assertClassImplements(JMSContext::class, StompContext::class);
    }

    public function testCouldBeCreatedWithRequiredArguments()
    {
        new StompContext($this->createStompClientMock());
    }

    public function testShouldCreateMessageInstance()
    {
        $context = new StompContext($this->createStompClientMock());

        $message = $context->createMessage('the body', ['key' => 'value'], ['hkey' => 'hvalue']);

        $this->assertInstanceOf(StompMessage::class, $message);
        $this->assertSame('the body', $message->getBody());
        $this->assertSame(['hkey' => 'hvalue'], $message->getHeaders());
        $this->assertSame(['key' => 'value'], $message->getProperties());
    }

    public function testShouldCreateQueueInstance()
    {
        $context = new StompContext($this->createStompClientMock());

        $queue = $context->createQueue('the name');

        $this->assertInstanceOf(StompDestination::class, $queue);
        $this->assertSame('the name', $queue->getQueueName());
        $this->assertSame('the name', $queue->getTopicName());
        $this->assertSame(StompDestination::TYPE_QUEUE, $queue->getType());
    }

    public function testShouldCreateTopicInstance()
    {
        $context = new StompContext($this->createStompClientMock());

        $topic = $context->createTopic('the name');

        $this->assertInstanceOf(StompDestination::class, $topic);
        $this->assertSame('the name', $topic->getQueueName());
        $this->assertSame('the name', $topic->getTopicName());
        $this->assertSame(StompDestination::TYPE_EXCHANGE, $topic->getType());
    }

    public function testThrowInvalidDestinationException()
    {
        $this->expectException(InvalidDestinationException::class);
        $this->expectExceptionMessage('The destination must be an instance of');

        $session = new StompContext($this->createStompClientMock());
        $session->createConsumer($this->createMock(Queue::class));
    }

    public function testShouldCreateMessageConsumerInstance()
    {
        $context = new StompContext($this->createStompClientMock());

        $this->assertInstanceOf(StompConsumer::class, $context->createConsumer(new StompDestination('')));
    }

    public function testShouldCreateMessageProducerInstance()
    {
        $context = new StompContext($this->createStompClientMock());

        $this->assertInstanceOf(StompProducer::class, $context->createProducer());
    }

    public function testShouldCloseConnections()
    {
        $client = $this->createStompClientMock();
        $client
            ->expects($this->once())
            ->method('disconnect')
        ;

        $context = new StompContext($client);

        $context->createProducer();
        $context->createConsumer(new StompDestination(''));

        $context->close();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|BufferedStompClient
     */
    private function createStompClientMock()
    {
        return $this->createMock(BufferedStompClient::class);
    }
}
