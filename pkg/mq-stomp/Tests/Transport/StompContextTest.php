<?php
namespace Formapro\Stomp\Tests\Transport;

use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\JMSContext;
use Formapro\Jms\Queue;
use Formapro\Stomp\Test\ClassExtensionTrait;
use Formapro\Stomp\Transport\BufferedStompClient;
use Formapro\Stomp\Transport\StompContext;
use Formapro\Stomp\Transport\StompDestination;
use Formapro\Stomp\Transport\StompMessage;
use Formapro\Stomp\Transport\StompConsumer;
use Formapro\Stomp\Transport\StompProducer;

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
        $this->assertSame('/queue/the name', $queue->getQueueName());
        $this->assertSame('/queue/the name', $queue->getTopicName());
        $this->assertSame(StompDestination::TYPE_QUEUE, $queue->getType());
    }

    public function testCreateQueueShouldCreateDestinationIfNameIsFullDestinationString()
    {
        $context = new StompContext($this->createStompClientMock());

        $destination = $context->createQueue('/amq/queue/name/routing-key');

        $this->assertInstanceOf(StompDestination::class, $destination);
        $this->assertEquals('amq/queue', $destination->getType());
        $this->assertEquals('name', $destination->getStompName());
        $this->assertEquals('routing-key', $destination->getRoutingKey());
        $this->assertEquals('/amq/queue/name/routing-key', $destination->getQueueName());
    }

    public function testShouldCreateTopicInstance()
    {
        $context = new StompContext($this->createStompClientMock());

        $topic = $context->createTopic('the name');

        $this->assertInstanceOf(StompDestination::class, $topic);
        $this->assertSame('/exchange/the name', $topic->getQueueName());
        $this->assertSame('/exchange/the name', $topic->getTopicName());
        $this->assertSame(StompDestination::TYPE_EXCHANGE, $topic->getType());
    }

    public function testCreateTopicShouldCreateDestinationIfNameIsFullDestinationString()
    {
        $context = new StompContext($this->createStompClientMock());

        $destination = $context->createTopic('/amq/queue/name/routing-key');

        $this->assertInstanceOf(StompDestination::class, $destination);
        $this->assertEquals('amq/queue', $destination->getType());
        $this->assertEquals('name', $destination->getStompName());
        $this->assertEquals('routing-key', $destination->getRoutingKey());
        $this->assertEquals('/amq/queue/name/routing-key', $destination->getQueueName());
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

        $this->assertInstanceOf(StompConsumer::class, $context->createConsumer(new StompDestination()));
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
        $context->createConsumer(new StompDestination());

        $context->close();
    }

    public function testCreateDestinationShouldThrowLogicExceptionIfTypeIsInvalid()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination name is invalid, cant find type: "/invalid-type/name"');

        $context = new StompContext($this->createStompClientMock());
        $context->createDestination('/invalid-type/name');
    }

    public function testCreateDestinationShouldThrowLogicExceptionIfExtraSlashFound()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination name is invalid, found extra / char: "/queue/name/routing-key/extra');

        $context = new StompContext($this->createStompClientMock());
        $context->createDestination('/queue/name/routing-key/extra');
    }

    public function testCreateDestinationShouldThrowLogicExceptionIfNameIsEmpty()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination name is invalid, name is empty: "/queue/"');

        $context = new StompContext($this->createStompClientMock());
        $context->createDestination('/queue/');
    }

    public function testCreateDestinationShouldThrowLogicExceptionIfRoutingKeyIsEmpty()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination name is invalid, routing key is empty: "/queue/name/"');

        $context = new StompContext($this->createStompClientMock());
        $context->createDestination('/queue/name/');
    }

    public function testCreateDestinationShouldParseStringAndCreateDestination()
    {
        $context = new StompContext($this->createStompClientMock());
        $destination = $context->createDestination('/amq/queue/name/routing-key');

        $this->assertEquals('amq/queue', $destination->getType());
        $this->assertEquals('name', $destination->getStompName());
        $this->assertEquals('routing-key', $destination->getRoutingKey());
        $this->assertEquals('/amq/queue/name/routing-key', $destination->getQueueName());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|BufferedStompClient
     */
    private function createStompClientMock()
    {
        return $this->createMock(BufferedStompClient::class);
    }
}
