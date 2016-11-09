<?php
namespace Formapro\MessageQueue\Tests\Unit\Router;

use Formapro\MessageQueue\Router\Recipient;
use Formapro\MessageQueue\Transport\Destination;
use Formapro\MessageQueue\Transport\MessageInterface;

class RecipientTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAllowGetMessageSetInConstructor()
    {
        $message = $this->createMock(MessageInterface::class);

        $recipient = new Recipient($this->createMock(Destination::class), $message);

        $this->assertSame($message, $recipient->getMessage());
    }

    public function testShouldAllowGetDestinationSetInConstructor()
    {
        $destination = $this->createMock(Destination::class);

        $recipient = new Recipient($destination, $this->createMock(MessageInterface::class));

        $this->assertSame($destination, $recipient->getDestination());
    }
}
