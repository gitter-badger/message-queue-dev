<?php
namespace Formapro\MessageQueue\Tests\Transport\Null;

use Formapro\Jms\JMSContext;
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
        $this->assertClassImplements(JMSContext::class, NullContext::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new NullContext();
    }

    public function testShouldAllowCreateMessageWithoutAnyArguments()
    {
        $session = new NullContext();

        $message = $session->createMessage();

        $this->assertInstanceOf(NullMessage::class, $message);

        $this->assertSame(null, $message->getBody());
        $this->assertSame([], $message->getHeaders());
        $this->assertSame([], $message->getProperties());
    }

    public function testShouldAllowCreateCustomMessage()
    {
        $session = new NullContext();

        $message = $session->createMessage('theBody', ['theProperty'], ['theHeader']);

        $this->assertInstanceOf(NullMessage::class, $message);

        $this->assertSame('theBody', $message->getBody());
        $this->assertSame(['theProperty'], $message->getProperties());
        $this->assertSame(['theHeader'], $message->getHeaders());
    }

    public function testShouldAllowCreateQueue()
    {
        $session = new NullContext();

        $queue = $session->createQueue('aName');

        $this->assertInstanceOf(NullQueue::class, $queue);
    }

    public function testShouldAllowCreateTopic()
    {
        $session = new NullContext();

        $topic = $session->createTopic('aName');

        $this->assertInstanceOf(NullTopic::class, $topic);
    }

    public function testShouldAllowCreateConsumerForGivenQueue()
    {
        $session = new NullContext();

        $queue = new NullQueue('aName');

        $consumer = $session->createConsumer($queue);

        $this->assertInstanceOf(NullConsumer::class, $consumer);
    }

    public function testShouldAllowCreateProducer()
    {
        $session = new NullContext();

        $producer = $session->createProducer();

        $this->assertInstanceOf(NullProducer::class, $producer);
    }

    public function testShouldDoNothingOnDeclareQueue()
    {
        $queue = new NullQueue('theQueueName');

        $session = new NullContext();
        $session->declareQueue($queue);
    }

    public function testShouldDoNothingOnDeclareTopic()
    {
        $topic = new NullTopic('theTopicName');

        $session = new NullContext();
        $session->declareTopic($topic);
    }

    public function testShouldDoNothingOnDeclareBind()
    {
        $topic = new NullTopic('theTopicName');
        $queue = new NullQueue('theQueueName');

        $session = new NullContext();
        $session->declareBind($topic, $queue);
    }
}
