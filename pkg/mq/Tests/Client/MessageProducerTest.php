<?php
namespace Formapro\MessageQueue\Tests\Client;

use Formapro\Fms\Queue;
use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessagePriority;
use Formapro\MessageQueue\Client\MessageProducer;
use Formapro\MessageQueue\Client\MessageProducerInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueue\Transport\Null\NullQueue;

class MessageProducerTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProducerInterface()
    {
        self::assertClassImplements(MessageProducerInterface::class, MessageProducer::class);
    }

    public function testCouldBeConstructedWithDriverAsFirstArgument()
    {
        new MessageProducer($this->createDriverStub());
    }

    public function testShouldCreateQueueAndSendMessage()
    {
        $config = new Config('', '', 'theRouterQueue', '', '');
        $queue = new NullQueue('therouterqueue');

        $message = new Message();

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('createQueue')
            ->with('therouterqueue')
            ->willReturn($queue)
        ;
        $driver
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', $message);
    }

    public function testShouldSendMessageToRouterProcessor()
    {
        $config = new Config('', 'theRouteMessageProcessor', 'theRouterQueue', '', '');
        $queue = new NullQueue('queue');

        $message = new Message();

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('createQueue')
            ->willReturn($queue)
        ;
        $driver
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $producer = new MessageProducer($driver);
        $producer->send('theTopic', $message);

        $expectedProperties = [
            'formapro.message_queue.client.topic_name' => 'theTopic',
            'formapro.message_queue.client.processor_name' => 'theRouteMessageProcessor',
            'formapro.message_queue.client.queue_name' => 'therouterqueue',
        ];

        self::assertEquals($expectedProperties, $message->getProperties());
    }

    public function testShouldSendMessageWithNormalPriorityByDefault()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new Message();

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', $message);

        self::assertSame(MessagePriority::NORMAL, $message->getPriority());
    }

    public function testShouldSendMessageWithCustomPriority()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new Message();
        $message->setPriority(MessagePriority::HIGH);

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', $message);

        self::assertSame(MessagePriority::HIGH, $message->getPriority());
    }

    public function testShouldSendMessageWithGeneratedMessageId()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new Message();

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', $message);

        self::assertNotEmpty($message->getMessageId());
    }

    public function testShouldSendMessageWithCustomMessageId()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new Message();
        $message->setMessageId('theCustomMessageId');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', $message);

        self::assertSame('theCustomMessageId', $message->getMessageId());
    }

    public function testShouldSendMessageWithGeneratedTimestamp()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new Message();

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', $message);

        self::assertNotEmpty($message->getTimestamp());
    }

    public function testShouldSendMessageWithCustomTimestamp()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new Message();
        $message->setTimestamp('theCustomTimestamp');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($message))
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', $message);

        self::assertSame('theCustomTimestamp', $message->getTimestamp());
    }

    public function testShouldSendStringAsPlainText()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Queue $queue, Message $message) {
                self::assertSame('theStringMessage', $message->getBody());
                self::assertSame('text/plain', $message->getContentType());
            })
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', 'theStringMessage');
    }

    public function testShouldSendArrayAsJsonString()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Queue $queue, Message $message) {
                self::assertSame('{"foo":"fooVal"}', $message->getBody());
                self::assertSame('application/json', $message->getContentType());
            })
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', ['foo' => 'fooVal']);
    }

    public function testShouldConvertMessageArrayBodyJsonString()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $message = new Message();
        $message->setBody(['foo' => 'fooVal']);

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Queue $queue, Message $message) {
                self::assertSame('{"foo":"fooVal"}', $message->getBody());
                self::assertSame('application/json', $message->getContentType());
            })
        ;

        $producer = new MessageProducer($driver);
        $producer->send('topic', $message);
    }

    public function testSendShouldForceScalarsToStringAndSetTextContentType()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Queue $queue, Message $message) {
                self::assertEquals('text/plain', $message->getContentType());

                self::assertInternalType('string', $message->getBody());
                self::assertEquals('12345', $message->getBody());
            })
        ;

        $producer = new MessageProducer($driver);
        $producer->send($queue, 12345);
    }

    public function testSendShouldForceMessageScalarsBodyToStringAndSetTextContentType()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');

        $message = new Message();
        $message->setBody(12345);

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Queue $queue, Message $message) {
                self::assertEquals('text/plain', $message->getContentType());

                self::assertInternalType('string', $message->getBody());
                self::assertEquals('12345', $message->getBody());
            })
        ;

        $producer = new MessageProducer($driver);
        $producer->send($queue, $message);
    }

    public function testSendShouldForceNullToEmptyStringAndSetTextContentType()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Queue $queue, Message $message) {
                self::assertEquals('text/plain', $message->getContentType());

                self::assertInternalType('string', $message->getBody());
                self::assertEquals('', $message->getBody());
            })
        ;

        $producer = new MessageProducer($driver);
        $producer->send($queue, null);
    }

    public function testSendShouldForceNullBodyToEmptyStringAndSetTextContentType()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('');

        $message = new Message();
        $message->setBody(null);

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Queue $queue, Message $message) {
                self::assertEquals('text/plain', $message->getContentType());

                self::assertInternalType('string', $message->getBody());
                self::assertEquals('', $message->getBody());
            })
        ;

        $producer = new MessageProducer($driver);
        $producer->send($queue, $message);
    }

    public function testShouldThrowExceptionIfBodyIsObjectOnSend()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->never())
            ->method('send')
        ;

        $producer = new MessageProducer($driver);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The message\'s body must be either null, scalar or array. Got: stdClass'
        );
        $producer->send('topic', new \stdClass());
    }

    public function testShouldThrowExceptionIfBodyIsArrayWithObjectsInsideOnSend()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->never())
            ->method('send')
        ;

        $producer = new MessageProducer($driver);

        $this->setExpectedException(
            \LogicException::class,
            'The message\'s body must be an array of scalars. Found not scalar in the array: stdClass'
        );
        $producer->send($queue, ['foo' => new \stdClass()]);
    }

    public function testShouldThrowExceptionIfBodyIsArrayWithObjectsInSubArraysInsideOnSend()
    {
        $config = new Config('', 'route-message-processor', 'router-queue', '', '');
        $queue = new NullQueue('queue');

        $driver = $this->createDriverStub($config, $queue);
        $driver
            ->expects($this->never())
            ->method('send')
        ;

        $producer = new MessageProducer($driver);

        $this->setExpectedException(
            \LogicException::class,
            'The message\'s body must be an array of scalars. Found not scalar in the array: stdClass'
        );
        $producer->send($queue, ['foo' => ['bar' => new \stdClass()]]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DriverInterface
     */
    protected function createDriverStub($config = null, $queue = null)
    {
        $driverMock = $this->createMock(DriverInterface::class);
        $driverMock
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config))
        ;
        $driverMock
            ->expects($this->any())
            ->method('createQueue')
            ->will($this->returnValue($queue))
        ;

        return $driverMock;
    }
}
