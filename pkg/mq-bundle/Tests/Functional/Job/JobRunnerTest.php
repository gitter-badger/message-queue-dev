<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\Job;

use Formapro\JobQueue\JobRunner;
use Formapro\MessageQueueBundle\Tests\Functional\WebTestCase;

class JobRunnerTest extends WebTestCase
{
    public function testCouldBeConstructedByContainer()
    {
        $instance = $this->container->get('formapro_message_queue.job.runner');

        $this->assertInstanceOf(JobRunner::class, $instance);
    }
}
