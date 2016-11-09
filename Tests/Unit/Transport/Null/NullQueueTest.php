<?php
namespace Formapro\MessageQueue\Tests\Unit\Transport\Null;

use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueue\Transport\Queue;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

class NullQueueTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementQueueInterface()
    {
        $this->assertClassImplements(Queue::class, NullQueue::class);
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
