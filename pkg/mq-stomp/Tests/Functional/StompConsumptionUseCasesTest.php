<?php
namespace Formapro\Stomp\Tests\Functional;

use Formapro\Fms\Context;
use Formapro\Fms\Message;
use Formapro\MessageQueue\Consumption\ChainExtension;
use Formapro\MessageQueue\Consumption\Extension\LimitConsumedMessagesExtension;
use Formapro\MessageQueue\Consumption\Extension\LimitConsumptionTimeExtension;
use Formapro\MessageQueue\Consumption\Extension\ReplyExtension;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Test\RabbitmqManagmentExtensionTrait;
use Formapro\MessageQueue\Test\RabbitmqStompExtension;
use Formapro\Stomp\StompContext;

class StompConsumptionUseCasesTest extends \PHPUnit_Framework_TestCase
{
    use RabbitmqStompExtension;
    use RabbitmqManagmentExtensionTrait;

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

    public function testConsumeOneMessageAndExit()
    {
        $queue = $this->stompContext->createQueue('stomp.test');

        $message = $this->stompContext->createMessage(__METHOD__);
        $this->stompContext->createProducer()->send($queue, $message);

        $queueConsumer = new QueueConsumer($this->stompContext, new ChainExtension([
            new LimitConsumedMessagesExtension(1),
            new LimitConsumptionTimeExtension(new \DateTime('+3sec')),
        ]));

        $processor = new StubMessageProcessor();
        $queueConsumer->bind($queue, $processor);

        $queueConsumer->consume();

        $this->assertInstanceOf(Message::class, $processor->lastProcessedMessage);
        $this->assertEquals(__METHOD__, $processor->lastProcessedMessage->getBody());
    }

    public function testConsumeOneMessageAndSendReplyExit()
    {
        $queue = $this->stompContext->createQueue('stomp.test');

        $replyQueue = $this->stompContext->createQueue('stomp.test_reply');

        $message = $this->stompContext->createMessage(__METHOD__);
        $message->setReplyTo($replyQueue->getQueueName());
        $this->stompContext->createProducer()->send($queue, $message);

        $queueConsumer = new QueueConsumer($this->stompContext, new ChainExtension([
            new LimitConsumedMessagesExtension(2),
            new LimitConsumptionTimeExtension(new \DateTime('+3sec')),
            new ReplyExtension(),
        ]));

        $replyMessage = $this->stompContext->createMessage(__METHOD__.'.reply');

        $processor = new StubMessageProcessor();
        $processor->result = Result::reply($replyMessage);

        $replyProcessor = new StubMessageProcessor();

        $queueConsumer->bind($queue, $processor);
        $queueConsumer->bind($replyQueue, $replyProcessor);
        $queueConsumer->consume();

        $this->assertInstanceOf(Message::class, $processor->lastProcessedMessage);
        $this->assertEquals(__METHOD__, $processor->lastProcessedMessage->getBody());

        $this->assertInstanceOf(Message::class, $replyProcessor->lastProcessedMessage);
        $this->assertEquals(__METHOD__.'.reply', $replyProcessor->lastProcessedMessage->getBody());
    }
}

class StubMessageProcessor implements MessageProcessorInterface
{
    public $result = Result::ACK;

    /** @var Message */
    public $lastProcessedMessage;

    public function process(Message $message, Context $context)
    {
        $this->lastProcessedMessage = $message;

        return $this->result;
    }
}
