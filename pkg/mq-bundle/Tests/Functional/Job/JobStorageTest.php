<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\Job;

use Formapro\JobQueue\JobStorage;
use Formapro\MessageQueueBundle\Tests\Functional\WebTestCase;

class JobStorageTest extends WebTestCase
{
    public function testCouldGetJobStorageAsServiceFromContainer()
    {
        $instance = $this->container->get('formapro_message_queue.job.storage');

        $this->assertInstanceOf(JobStorage::class, $instance);
    }
}
