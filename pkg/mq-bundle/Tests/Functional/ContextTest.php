<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\Fms\Context;

/**
 * @group functional
 */
class ContextTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $connection = $this->container->get('formapro_message_queue.transport.context');

        $this->assertInstanceOf(Context::class, $connection);
    }
}
