<?php
namespace FormaPro\MessageQueue\Tests\Unit\Transport\Null;

use FormaPro\MessageQueue\Transport\MessageProducerInterface;
use FormaPro\MessageQueue\Transport\Null\NullMessage;
use FormaPro\MessageQueue\Transport\Null\NullMessageProducer;
use FormaPro\MessageQueue\Transport\Null\NullTopic;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class NullMessageProducerTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProducerInterface()
    {
        $this->assertClassImplements(MessageProducerInterface::class, NullMessageProducer::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new NullMessageProducer();
    }

    public function testShouldDoNothingOnSend()
    {
        $producer = new NullMessageProducer();

        $producer->send(new NullTopic('aName'), new NullMessage());
    }
}
