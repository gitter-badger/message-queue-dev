<?php
namespace Formapro\MessageQueueStompTransport\Tests\Transport;

use Formapro\MessageQueue\Transport\QueueInterface;
use Formapro\MessageQueue\Transport\TopicInterface;
use Formapro\MessageQueueStompTransport\Test\ClassExtensionTrait;
use Formapro\MessageQueueStompTransport\Transport\StompDestination;

class StompDestinationTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementsTopicAndQueueInterfaces()
    {
        $this->assertClassImplements(TopicInterface::class, StompDestination::class);
        $this->assertClassImplements(QueueInterface::class, StompDestination::class);
    }

    public function testShouldReturnRealNameAsQueueByDefault()
    {
        $destination = new StompDestination('name');

        $this->assertEquals('/queue/name', $destination->getRealName());
    }

    public function testShouldReturnRealNameAsQueue()
    {
        $destination = new StompDestination('name');
        $destination->setType(StompDestination::TYPE_QUEUE);

        $this->assertEquals('/queue/name', $destination->getRealName());
    }

    public function testShouldReturnRealNameAsTopic()
    {
        $destination = new StompDestination('name');
        $destination->setType(StompDestination::TYPE_TOPIC);

        $this->assertEquals('/topic/name', $destination->getRealName());
    }

    public function testShouldReturnRealNameAsExchange()
    {
        $destination = new StompDestination('name');
        $destination->setType(StompDestination::TYPE_TOPIC);

        $this->assertEquals('/topic/name', $destination->getRealName());
    }

    public function testShouldReturnRealNameAsExchangeWithRoutingKey()
    {
        $destination = new StompDestination('name');
        $destination->setType(StompDestination::TYPE_EXCHANGE);
        $destination->setRoutingKey('routing-key');

        $this->assertEquals('/exchange/name/routing-key', $destination->getRealName());
    }
}
