<?php
namespace Formapro\MessageQueueStompTransport\Tests\DependencyInjection;

use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompContext;
use Formapro\MessageQueueStompTransport\Transport\StompMessage;

class StompCommonUseCasesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StompContext
     */
    private $stompContext;

    public function setUp()
    {
        if (false == getenv('RABBITMQ_HOST')) {
            throw new \PHPUnit_Framework_SkippedTestError('Functional tests are not allowed in this environment');
        }

        $rabbitmqHost = getenv('RABBITMQ_HOST');
        $rabbitmqUser = getenv('RABBITMQ_USER');
        $rabbitmqPort = getenv('RABBITMQ_STOMP_PORT');
        $rabbitmqPassword = getenv('RABBITMQ_PASSWORD');
        $rabbitmqVhost = getenv('RABBITMQ_VHOST');

        $stomp = new BufferedStompClient("tcp://$rabbitmqHost:$rabbitmqPort");
        $stomp->setLogin($rabbitmqUser, $rabbitmqPassword);
        $stomp->setVhostname($rabbitmqVhost);
        $stomp->setSync(false);

        $this->stompContext = new StompContext($stomp);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$rabbitmqUser:$rabbitmqPassword@{$rabbitmqHost}:15672/api/queues/{$rabbitmqVhost}/stomp.test/contents");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertTrue(in_array($httpCode, [204, 404]));

        curl_close($ch);
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

        $consumer = $this->stompContext->createConsumer($queue);
        $message = $consumer->receiveNoWait();

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
}