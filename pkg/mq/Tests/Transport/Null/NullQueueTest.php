<?php
namespace Formapro\MessageQueue\Tests\Transport\Null;

use Formapro\Jms\Queue;
use Formapro\MessageQueue\Transport\Null\NullQueue;
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
