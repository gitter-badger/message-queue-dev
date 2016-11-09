<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\Client;

use Formapro\MessageQueue\Client\MessageProducerInterface;
use Formapro\MessageQueueBundle\Tests\Functional\WebTestCase;

class MessageProducerTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $messageProducer = $this->container->get('formapro_message_queue.client.message_producer');

        $this->assertInstanceOf(MessageProducerInterface::class, $messageProducer);
    }

    public function testCouldBeGetFromContainerAsShortenAlias()
    {
        $messageProducer = $this->container->get('formapro_message_queue.client.message_producer');
        $aliasMessageProducer = $this->container->get('formapro_message_queue.message_producer');

        $this->assertSame($messageProducer, $aliasMessageProducer);
    }
}
