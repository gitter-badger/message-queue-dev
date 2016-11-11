<?php
namespace Formapro\MessageQueue\Tests\Router;

use Formapro\Jms\Destination;
use Formapro\Jms\Message;
use Formapro\MessageQueue\Router\Recipient;

class RecipientTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAllowGetMessageSetInConstructor()
    {
        $message = $this->createMock(Message::class);

        $recipient = new Recipient($this->createMock(Destination::class), $message);

        $this->assertSame($message, $recipient->getMessage());
    }

    public function testShouldAllowGetDestinationSetInConstructor()
    {
        $destination = $this->createMock(Destination::class);

        $recipient = new Recipient($destination, $this->createMock(Message::class));

        $this->assertSame($destination, $recipient->getDestination());
    }
}
