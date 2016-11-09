<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Consumption\QueueConsumer;

class QueueConsumerTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $queueConsumer = $this->container->get('formapro_message_queue.consumption.queue_consumer');
        
        $this->assertInstanceOf(QueueConsumer::class, $queueConsumer);
    }
}
