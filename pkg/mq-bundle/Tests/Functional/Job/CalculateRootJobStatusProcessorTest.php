<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\Job;

use Formapro\MessageQueueBundle\Tests\Functional\WebTestCase;
use Formapro\MessageQueueJob\Job\CalculateRootJobStatusProcessor;

class CalculateRootJobStatusProcessorTest extends WebTestCase
{
    public function testCouldBeConstructedByContainer()
    {
        $this->markTestSkipped('Jobs is not ready');

        $instance = $this->container->get('formapro_message_queue.job.calculate_root_job_status_processor');

        $this->assertInstanceOf(CalculateRootJobStatusProcessor::class, $instance);
    }
}
