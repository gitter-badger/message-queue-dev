<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\Job;

use Formapro\MessageQueueBundle\Tests\Functional\WebTestCase;
use Formapro\MessageQueueJob\Job\JobRunner;

class JobRunnerTest extends WebTestCase
{
    public function testCouldBeConstructedByContainer()
    {
        $this->markTestSkipped('Jobs is not ready');

        $instance = $this->container->get('formapro_message_queue.job.runner');

        $this->assertInstanceOf(JobRunner::class, $instance);
    }
}
