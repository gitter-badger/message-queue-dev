<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\Meta\DestinationsCommand;

class DestinationsCommandTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $command = $this->container->get('formapro_message_queue.client.meta.destinations_command');

        $this->assertInstanceOf(DestinationsCommand::class, $command);
    }
}
