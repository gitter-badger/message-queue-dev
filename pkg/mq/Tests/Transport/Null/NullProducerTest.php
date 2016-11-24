<?php
namespace Formapro\MessageQueue\Tests\Transport\Null;

use Formapro\Fms\Producer;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullProducer;
use Formapro\MessageQueue\Transport\Null\NullTopic;

class NullProducerTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProducerInterface()
    {
        $this->assertClassImplements(Producer::class, NullProducer::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new NullProducer();
    }

    public function testShouldDoNothingOnSend()
    {
        $producer = new NullProducer();

        $producer->send(new NullTopic('aName'), new NullMessage());
    }
}
