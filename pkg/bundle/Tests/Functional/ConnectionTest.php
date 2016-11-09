<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Transport\ConnectionInterface;

class ConnectionTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $connection = $this->container->get('formapro_message_queue.transport.connection');
        
        $this->assertInstanceOf(ConnectionInterface::class, $connection);
    }
}
