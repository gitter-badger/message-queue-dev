<?php
namespace Formapro\MessageQueueStompTransport\Tests\DependencyInjection;

use Formapro\MessageQueue\Rpc\Promise;
use Formapro\MessageQueue\Rpc\RpcClient;
use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompContext;
use Formapro\MessageQueueStompTransport\Transport\StompMessage;

class StompRpcUseCasesTest extends \PHPUnit_Framework_TestCase
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
        curl_setopt($ch, CURLOPT_URL, "http://$rabbitmqUser:$rabbitmqPassword@{$rabbitmqHost}:15672/api/queues/{$rabbitmqVhost}/stomp.rpc.test/contents");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertTrue(in_array($httpCode, [204, 404]));

        curl_close($ch);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$rabbitmqUser:$rabbitmqPassword@{$rabbitmqHost}:15672/api/queues/{$rabbitmqVhost}/stomp.rpc.reply_test/contents");
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

    public function testDoAsyncRpcCallWithCustomReplyQueue()
    {
        $queue = $this->stompContext->createQueue('stomp.rpc.test');
        $replyQueue = $this->stompContext->createQueue('stomp.rpc.reply_test');
        $queue->setDurable(true);
        $queue->setAutoDelete(false);

        $rpcClient = new RpcClient($this->stompContext);

        $message = $this->stompContext->createMessage();
        $message->setReplyTo($replyQueue->getQueueName());

        $promise = $rpcClient->callAsync($queue, $message, 10);
        $this->assertInstanceOf(Promise::class, $promise);

        $consumer = $this->stompContext->createConsumer($queue);
        $message = $consumer->receive(1);
        $this->assertInstanceOf(StompMessage::class, $message);
        $this->assertNotNull($message->getReplyTo());
        $this->assertNotNull($message->getCorrelationId());
        $consumer->acknowledge($message);

        $replyQueue = $this->stompContext->createQueue($message->getReplyTo());
        $replyMessage = $this->stompContext->createMessage('This a reply!');
        $replyMessage->setCorrelationId($message->getCorrelationId());

        $this->stompContext->createProducer()->send($replyQueue, $replyMessage);

        $actualReplyMessage = $promise->getMessage();
        $this->assertInstanceOf(StompMessage::class, $actualReplyMessage);
    }

    public function testDoAsyncRecCallWithCastInternallyCreatedTemporaryReplyQueue()
    {
        $queue = $this->stompContext->createQueue('stomp.rpc.test');
        $queue->setDurable(true);
        $queue->setAutoDelete(false);

        $rpcClient = new RpcClient($this->stompContext);

        $message = $this->stompContext->createMessage();

        $promise = $rpcClient->callAsync($queue, $message, 10);
        $this->assertInstanceOf(Promise::class, $promise);

        $consumer = $this->stompContext->createConsumer($queue);
        $message = $consumer->receive(1);
        $this->assertInstanceOf(StompMessage::class, $message);
        $this->assertNotNull($message->getReplyTo());
        $this->assertNotNull($message->getCorrelationId());
        $consumer->acknowledge($message);

        $replyQueue = $this->stompContext->createQueue($message->getReplyTo());
        $replyMessage = $this->stompContext->createMessage('This a reply!');
        $replyMessage->setCorrelationId($message->getCorrelationId());

        $this->stompContext->createProducer()->send($replyQueue, $replyMessage);

        $actualReplyMessage = $promise->getMessage();
        $this->assertInstanceOf(StompMessage::class, $actualReplyMessage);
    }
}