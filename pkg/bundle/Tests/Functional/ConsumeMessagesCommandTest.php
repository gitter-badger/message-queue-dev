<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\ConsumeMessagesCommand;

class ConsumeMessagesCommandTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $command = $this->container->get('formapro_message_queue.client.consume_messages_command');

        $this->assertInstanceOf(ConsumeMessagesCommand::class, $command);
    }
}
