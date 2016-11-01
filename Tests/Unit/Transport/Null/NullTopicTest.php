<?php
namespace Formapro\MessageQueue\Tests\Unit\Transport\Null;

use Formapro\MessageQueue\Transport\Null\NullTopic;
use Formapro\MessageQueue\Transport\TopicInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

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
