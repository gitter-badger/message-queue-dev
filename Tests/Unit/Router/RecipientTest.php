<?php
namespace FormaPro\MessageQueue\Tests\Unit\Router;

use FormaPro\MessageQueue\Router\Recipient;
use FormaPro\MessageQueue\Transport\DestinationInterface;
use FormaPro\MessageQueue\Transport\MessageInterface;

class RecipientTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAllowGetMessageSetInConstructor()
    {
        $message = $this->createMock(MessageInterface::class);

        $recipient = new Recipient($this->createMock(DestinationInterface::class), $message);

        $this->assertSame($message, $recipient->getMessage());
    }

    public function testShouldAllowGetDestinationSetInConstructor()
    {
        $destination = $this->createMock(DestinationInterface::class);

        $recipient = new Recipient($destination, $this->createMock(MessageInterface::class));

        $this->assertSame($destination, $recipient->getDestination());
    }
}
