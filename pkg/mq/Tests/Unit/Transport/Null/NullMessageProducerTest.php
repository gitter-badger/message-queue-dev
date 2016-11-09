<?php
namespace Formapro\MessageQueue\Tests\Unit\Transport\Null;

use Formapro\MessageQueue\Transport\MessageProducerInterface;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullMessageProducer;
use Formapro\MessageQueue\Transport\Null\NullTopic;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

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
