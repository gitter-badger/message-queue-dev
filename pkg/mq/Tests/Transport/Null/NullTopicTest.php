<?php
namespace Formapro\MessageQueue\Tests\Transport\Null;

use Formapro\Jms\Topic;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueue\Transport\Null\NullTopic;

class NullTopicTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementTopicInterface()
    {
        $this->assertClassImplements(Topic::class, NullTopic::class);
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
