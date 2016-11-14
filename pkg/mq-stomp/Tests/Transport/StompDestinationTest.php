<?php
namespace Formapro\Stomp\Tests\Transport;

use Formapro\Jms\Queue;
use Formapro\Jms\Topic;
use Formapro\Stomp\Test\ClassExtensionTrait;
use Formapro\Stomp\Transport\StompDestination;

class StompDestinationTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementsTopicAndQueueInterfaces()
    {
        $this->assertClassImplements(Topic::class, StompDestination::class);
        $this->assertClassImplements(Queue::class, StompDestination::class);
    }

    public function testShouldParseDestinationStringWithRoutingKey()
    {
        $destination = new StompDestination();
        $destination->setType(StompDestination::TYPE_AMQ_QUEUE);
        $destination->setStompName('name');
        $destination->setRoutingKey('routing-key');

        $this->assertSame(StompDestination::TYPE_AMQ_QUEUE, $destination->getType());
        $this->assertSame('name', $destination->getStompName());
        $this->assertSame('routing-key', $destination->getRoutingKey());
        $this->assertSame('/amq/queue/name/routing-key', $destination->getQueueName());
    }

    public function testShouldParseDestinationStringWithoutRoutingKey()
    {
        $destination = new StompDestination();
        $destination->setType(StompDestination::TYPE_TOPIC);
        $destination->setStompName('name');

        $this->assertSame(StompDestination::TYPE_TOPIC, $destination->getType());
        $this->assertSame('name', $destination->getStompName());
        $this->assertNull($destination->getRoutingKey());
        $this->assertSame('/topic/name', $destination->getQueueName());
    }

    public function testShouldThrowLogicExceptionIfTypeIsInvalid()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination name is invalid, cant find type: "/invalid-type/name"');

        $destination = new StompDestination();
        $destination->setQueueName('/invalid-type/name');
    }

    public function testShouldThrowLogicExceptionIfExtraSlashFound()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination name is invalid, found extra / char: "/queue/name/routing-key/extra');

        $destination = new StompDestination();
        $destination->setQueueName('/queue/name/routing-key/extra');
    }

    public function testShouldThrowLogicExceptionIfNameIsEmpty()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination name is invalid, name is empty: "/queue/"');


        $destination = new StompDestination();
        $destination->setQueueName('/queue/');
    }

    public function testShouldThrowLogicExceptionIfRoutingKeyIsEmpty()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination name is invalid, routing key is empty: "/queue/name/"');

        $destination = new StompDestination();
        $destination->setQueueName('/queue/name/');
    }

    public function testShouldThrowLogicExceptionIfNameIsNotSet()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination type or name is not set');

        $destination = new StompDestination();
        $destination->setType(StompDestination::TYPE_QUEUE);

        $destination->getQueueName();
    }

    public function testShouldThrowLogicExceptionIfTypeIsNotSet()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Destination type or name is not set');

        $destination = new StompDestination();
        $destination->setStompName('name');

        $destination->getQueueName();
    }

    public function testSetTypeShouldThrowLogicExceptionIfTypeIsInvalid()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid destination type: "invalid-type"');

        $destination = new StompDestination();
        $destination->setType('invalid-type');
    }
}
