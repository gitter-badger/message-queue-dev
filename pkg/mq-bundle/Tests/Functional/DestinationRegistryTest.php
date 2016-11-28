<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\Meta\DestinationMetaRegistry;

/**
 * @group functional
 */
class DestinationRegistryTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $connection = $this->container->get('formapro_message_queue.client.meta.destination_meta_registry');

        $this->assertInstanceOf(DestinationMetaRegistry::class, $connection);
    }
}
