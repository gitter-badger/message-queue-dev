<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\Jms\JMSContext;

class ContextTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $connection = $this->container->get('formapro_message_queue.transport.context');
        
        $this->assertInstanceOf(JMSContext::class, $connection);
    }
}
