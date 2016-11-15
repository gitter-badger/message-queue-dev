<?php
namespace Formapro\MessageQueueStompTransport\Tests\Functional;

use Formapro\Stomp\Test\StompExtensionTrait;
use Formapro\Stomp\Transport\StompContext;
use Formapro\Stomp\Transport\StompMessage;

class StompCommonUseCasesTest extends \PHPUnit_Framework_TestCase
{
    use StompExtensionTrait;

    /**
     * @var StompContext
     */
    private $stompContext;

    public function setUp()
    {
        $this->stompContext = $this->buildStompContext();

        $this->removeQueue('stomp.test');
    }

    public function tearDown()
    {
        $this->stompContext->close();
    }

    public function testWaitsForTwoSecondsAndReturnNullOnReceive()
    {
        $queue = $this->stompContext->createQueue('stomp.test');
        $queue->setDurable(true);
        $queue->setAutoDelete(false);

        $startAt = microtime(true);

        $consumer = $this->stompContext->createConsumer($queue);
        $message = $consumer->receive(2);

        $endAt = microtime(true);

        $this->assertNull($message);

        $this->assertGreaterThan(1.5, $endAt - $startAt);
        $this->assertLessThan(2.5, $endAt - $startAt);
    }

    public function testReturnNullImmediatelyOnReceiveNoWait()
    {
        $queue = $this->stompContext->createQueue('stomp.test');
        $queue->setDurable(true);
        $queue->setAutoDelete(false);

        $startAt = microtime(true);

        $consumer = $this->stompContext->createConsumer($queue);
        $message = $consumer->receiveNoWait();

        $endAt = microtime(true);

        $this->assertNull($message);

        $this->assertLessThan(0.5, $endAt - $startAt);
    }

    public function testProduceAndReceiveOneMessage()
    {
        $queue = $this->stompContext->createQueue('stomp.test');
        $queue->setDurable(true);
        $queue->setAutoDelete(false);

        $message = $this->stompContext->createMessage(
            __METHOD__,
            ['FooProperty' => 'FooVal'],
            ['BarHeader' => 'BarVal']
        );

        $producer = $this->stompContext->createProducer();
        $producer->send($queue, $message);

        usleep(100);

        $consumer = $this->stompContext->createConsumer($queue);
        $message = $consumer->receive(1);

        $this->assertInstanceOf(StompMessage::class, $message);
        $consumer->acknowledge($message);

        $this->assertEquals(__METHOD__, $message->getBody());
        $this->assertEquals(['FooProperty' => 'FooVal'], $message->getProperties());
        $this->assertEquals([
            'exclusive' => false,
            'auto-delete' => false,
            'durable' => true,
            'BarHeader' => 'BarVal',
        ], $message->getHeaders());
    }

    public function testProduceAndReceiveNoWaitOneMessage()
    {
        $queue = $this->stompContext->createQueue('stomp.test');
        $queue->setDurable(true);
        $queue->setAutoDelete(false);

        $message = $this->stompContext->createMessage(__METHOD__);

        $producer = $this->stompContext->createProducer();
        $producer->send($queue, $message);

        usleep(200);

        $stompContext = $this->buildStompContext();

        $consumer = $stompContext->createConsumer($queue);
        $message = $consumer->receiveNoWait();

        $this->assertInstanceOf(StompMessage::class, $message);
        $consumer->acknowledge($message);

        $this->assertEquals(__METHOD__, $message->getBody());
    }
}
