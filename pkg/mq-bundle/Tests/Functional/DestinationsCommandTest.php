<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\Meta\DestinationsCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class DestinationsCommandTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $command = $this->container->get('formapro_message_queue.client.meta.destinations_command');

        $this->assertInstanceOf(DestinationsCommand::class, $command);
    }

    public function testShouldDisplayRegisteredDestionations()
    {
        $command = $this->container->get('formapro_message_queue.client.meta.destinations_command');

        $tester = new CommandTester($command);
        $tester->execute([]);

        $display = $tester->getDisplay();

        $this->assertContains(' default ', $display);
        $this->assertContains('formapro.default', $display);
        $this->assertContains('formapro_message_queue.client.route_message_processor', $display);
    }
}
