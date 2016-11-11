<?php
namespace Formapro\MessageQueueStompTransport\Tests\Transport;

use Formapro\Jms\Queue;
use Formapro\Jms\Topic;
use Formapro\MessageQueueStompTransport\Test\ClassExtensionTrait;
use Formapro\MessageQueueStompTransport\Transport\StompDestination;

class StompDestinationTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementsTopicAndQueueInterfaces()
    {
        $this->assertClassImplements(Topic::class, StompDestination::class);
        $this->assertClassImplements(Queue::class, StompDestination::class);
    }

    public function testShouldReturnRealNameAsQueueByDefault()
    {
        $destination = new StompDestination('name');

        $this->assertEquals('/queue/name', $destination->getStompName());
    }

    public function testShouldReturnRealNameAsQueue()
    {
        $destination = new StompDestination('name');
        $destination->setType(StompDestination::TYPE_QUEUE);

        $this->assertEquals('/queue/name', $destination->getStompName());
    }

    public function testShouldReturnRealNameAsTopic()
    {
        $destination = new StompDestination('name');
        $destination->setType(StompDestination::TYPE_TOPIC);

        $this->assertEquals('/topic/name', $destination->getStompName());
    }

    public function testShouldReturnRealNameAsExchange()
    {
        $destination = new StompDestination('name');
        $destination->setType(StompDestination::TYPE_TOPIC);

        $this->assertEquals('/topic/name', $destination->getStompName());
    }

    public function testShouldReturnRealNameAsExchangeWithRoutingKey()
    {
        $destination = new StompDestination('name');
        $destination->setType(StompDestination::TYPE_EXCHANGE);
        $destination->setRoutingKey('routing-key');

        $this->assertEquals('/exchange/name/routing-key', $destination->getStompName());
    }
}
