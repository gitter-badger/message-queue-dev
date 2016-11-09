<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\Client;

use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueueBundle\Tests\Functional\WebTestCase;

class DriverTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $driver = $this->container->get('formapro_message_queue.client.driver');

        $this->assertInstanceOf(DriverInterface::class, $driver);
    }
}
