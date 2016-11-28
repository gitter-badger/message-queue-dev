<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\Meta\TopicsCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class TopicsCommandTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $command = $this->container->get('formapro_message_queue.client.meta.topics_command');

        $this->assertInstanceOf(TopicsCommand::class, $command);
    }

    public function testShouldDisplayRegisteredTopics()
    {
        $command = $this->container->get('formapro_message_queue.client.meta.topics_command');

        $tester = new CommandTester($command);
        $tester->execute([]);

        $display = $tester->getDisplay();

        $this->assertContains('formapro_message_queue.route_message', $display);
        $this->assertContains('formapro_message_queue.client.route_message_processor', $display);
    }
}
