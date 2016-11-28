<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\Meta\TopicMetaRegistry;

/**
 * @group functional
 */
class TopicRegistryTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $connection = $this->container->get('formapro_message_queue.client.meta.topic_meta_registry');

        $this->assertInstanceOf(TopicMetaRegistry::class, $connection);
    }
}
