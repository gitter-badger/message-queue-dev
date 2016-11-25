<?php
namespace Formapro\MessageQueue\Tests\Transport\Null;

use Formapro\Fms\Context;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueue\Transport\Null\NullConsumer;
use Formapro\MessageQueue\Transport\Null\NullContext;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullProducer;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueue\Transport\Null\NullTopic;

class NullContextTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementSessionInterface()
    {
        $this->assertClassImplements(Context::class, NullContext::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new NullContext();
    }

    public function testShouldAllowCreateMessageWithoutAnyArguments()
    {
        $context = new NullContext();

        $message = $context->createMessage();

        $this->assertInstanceOf(NullMessage::class, $message);

        $this->assertSame(null, $message->getBody());
        $this->assertSame([], $message->getHeaders());
        $this->assertSame([], $message->getProperties());
    }

    public function testShouldAllowCreateCustomMessage()
    {
        $context = new NullContext();

        $message = $context->createMessage('theBody', ['theProperty'], ['theHeader']);

        $this->assertInstanceOf(NullMessage::class, $message);

        $this->assertSame('theBody', $message->getBody());
        $this->assertSame(['theProperty'], $message->getProperties());
        $this->assertSame(['theHeader'], $message->getHeaders());
    }

    public function testShouldAllowCreateQueue()
    {
        $context = new NullContext();

        $queue = $context->createQueue('aName');

        $this->assertInstanceOf(NullQueue::class, $queue);
    }

    public function testShouldAllowCreateTopic()
    {
        $context = new NullContext();

        $topic = $context->createTopic('aName');

        $this->assertInstanceOf(NullTopic::class, $topic);
    }

    public function testShouldAllowCreateConsumerForGivenQueue()
    {
        $context = new NullContext();

        $queue = new NullQueue('aName');

        $consumer = $context->createConsumer($queue);

        $this->assertInstanceOf(NullConsumer::class, $consumer);
    }

    public function testShouldAllowCreateProducer()
    {
        $context = new NullContext();

        $producer = $context->createProducer();

        $this->assertInstanceOf(NullProducer::class, $producer);
    }

    public function testShouldDoNothingOnDeclareQueue()
    {
        $queue = new NullQueue('theQueueName');

        $context = new NullContext();
        $context->declareQueue($queue);
    }

    public function testShouldDoNothingOnDeclareTopic()
    {
        $topic = new NullTopic('theTopicName');

        $context = new NullContext();
        $context->declareTopic($topic);
    }

    public function testShouldDoNothingOnDeclareBind()
    {
        $topic = new NullTopic('theTopicName');
        $queue = new NullQueue('theQueueName');

        $context = new NullContext();
        $context->declareBind($topic, $queue);
    }

    public function testShouldCreateTempraryQueueWithUnqiueName()
    {
        $context = new NullContext();

        $firstTmpQueue = $context->createTemporaryQueue();
        $secondTmpQueue = $context->createTemporaryQueue();

        $this->assertInstanceOf(NullQueue::class, $firstTmpQueue);
        $this->assertInstanceOf(NullQueue::class, $secondTmpQueue);

        $this->assertNotEquals($firstTmpQueue->getQueueName(), $secondTmpQueue->getQueueName());
    }
}
