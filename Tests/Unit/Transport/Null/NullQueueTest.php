<?php
namespace FormaPro\MessageQueue\Tests\Unit\Transport\Null;

use FormaPro\MessageQueue\Transport\Null\NullQueue;
use FormaPro\MessageQueue\Transport\QueueInterface;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class NullQueueTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementQueueInterface()
    {
        $this->assertClassImplements(QueueInterface::class, NullQueue::class);
    }

    public function testCouldBeConstructedWithNameAsArgument()
    {
        new NullQueue('aName');
    }

    public function testShouldAllowGetNameSetInConstructor()
    {
        $queue = new NullQueue('theName');

        $this->assertEquals('theName', $queue->getQueueName());
    }
}
