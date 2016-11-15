<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\Job;

use Formapro\MessageQueueBundle\Tests\Functional\WebTestCase;
use Formapro\JobQueue\CalculateRootJobStatusProcessor;

class CalculateRootJobStatusProcessorTest extends WebTestCase
{
    public function testCouldBeConstructedByContainer()
    {
        $instance = $this->container->get('formapro_message_queue.job.calculate_root_job_status_processor');

        $this->assertInstanceOf(CalculateRootJobStatusProcessor::class, $instance);
    }
}
