<?php
namespace FormaPro\MessageQueue\Tests\Unit\Client;

use FormaPro\MessageQueue\Client\MessagePriority;

class MessagePriorityTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldVeryLowPriorityHasExpectedValue()
    {
        $this->assertSame('formapro.message_queue.client.very_low_message_priority', MessagePriority::VERY_LOW);
    }

    public function testShouldLowPriorityHasExpectedValue()
    {
        $this->assertSame('formapro.message_queue.client.low_message_priority', MessagePriority::LOW);
    }

    public function testShouldMediumPriorityHasExpectedValue()
    {
        $this->assertSame('formapro.message_queue.client.normal_message_priority', MessagePriority::NORMAL);
    }

    public function testShouldHighPriorityHasExpectedValue()
    {
        $this->assertSame('formapro.message_queue.client.high_message_priority', MessagePriority::HIGH);
    }

    public function testShouldVeryHighPriorityHasExpectedValue()
    {
        $this->assertSame('formapro.message_queue.client.very_high_message_priority', MessagePriority::VERY_HIGH);
    }
}
