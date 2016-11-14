<?php
namespace Formapro\MessageQueueStompTransport\Tests\Functional;

use Formapro\MessageQueue\Rpc\Promise;
use Formapro\MessageQueue\Rpc\RpcClient;
use Formapro\Stomp\Test\StompExtensionTrait;
use Formapro\Stomp\Transport\StompContext;
use Formapro\Stomp\Transport\StompMessage;

class StompRpcUseCasesTest extends \PHPUnit_Framework_TestCase
{
    use StompExtensionTrait;

    /**
     * @var StompContext
     */
    private $stompContext;

    public function setUp()
    {
        $this->stompContext = $this->buildStompContext();

        $this->removeQueue('stomp.rpc.test');
        $this->removeQueue('stomp.rpc.reply_test');
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