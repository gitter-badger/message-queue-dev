<?php
namespace FormaPro\MessageQueue\Tests\Unit\Transport\Null;

use FormaPro\MessageQueue\Transport\Null\NullTopic;
use FormaPro\MessageQueue\Transport\TopicInterface;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class NullTopicTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementTopicInterface()
    {
        $this->assertClassImplements(TopicInterface::class, NullTopic::class);
    }

    public function testCouldBeConstructedWithNameAsArgument()
    {
        new NullTopic('aName');
    }

    public function testShouldAllowGetNameSetInConstructor()
    {
        $topic = new NullTopic('theName');

        $this->assertEquals('theName', $topic->getTopicName());
    }
}
