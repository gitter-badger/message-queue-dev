<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\Job;

use Formapro\MessageQueueBundle\Tests\Functional\WebTestCase;
use Formapro\MessageQueueJob\Job\DependentJobService;

class DependentJobServiceTest extends WebTestCase
{
    public function testCouldBeConstructedByContainer()
    {
        $instance = $this->container->get('formapro_message_queue.job.dependent_job_service');

        $this->assertInstanceOf(DependentJobService::class, $instance);
    }
}
