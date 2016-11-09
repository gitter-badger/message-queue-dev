<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\Meta\TopicsCommand;

class TopicsCommandTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $command = $this->container->get('formapro_message_queue.client.meta.topics_command');

        $this->assertInstanceOf(TopicsCommand::class, $command);
    }
}
