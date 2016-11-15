<?php
namespace Formapro\JobQueue\Tests;

use Formapro\Jms\JMSContext;
use Formapro\JobQueue\DependentJobMessageProcessor;
use Formapro\JobQueue\Job;
use Formapro\JobQueue\JobStorage;
use Formapro\JobQueue\Topics;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessageProducerInterface;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Psr\Log\LoggerInterface;

class DependentJobMessageProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldReturnSubscribedTopicNames()
    {
        $this->assertEquals(
            [Topics::ROOT_JOB_STOPPED],
            DependentJobMessageProcessor::getSubscribedTopics()
        );
    }

    public function testShouldLogCriticalAndRejectMessageIfJobIdIsNotSet()
    {
        $jobStorage = $this->createJobStorageMock();

        $producer = $this->createMessageProducerMock();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('[DependentJobMessageProcessor] Got invalid message. body: "{"key":"value"}"')
        ;

        $message = new NullMessage();
        $message->setBody(json_encode(['key' => 'value']));

        $processor = new DependentJobMessageProcessor($jobStorage, $producer, $logger);

        $result = $processor->process($message, $this->createContextMock());

        $this->assertEquals(Result::REJECT, $result);
    }

    public function testShouldLogCriticalAndRejectMessageIfJobEntityWasNotFound()
    {
        $jobStorage = $this->createJobStorageMock();
        $jobStorage
            ->expects($this->once())
            ->method('findJobById')
            ->with(12345)
        ;

        $producer = $this->createMessageProducerMock();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('[DependentJobMessageProcessor] Job was not found. id: "12345"')
        ;

        $message = new NullMessage();
        $message->setBody(json_encode(['jobId' => 12345]));

        $processor = new DependentJobMessageProcessor($jobStorage, $producer, $logger);

        $result = $processor->process($message, $this->createContextMock());

        $this->assertEquals(Result::REJECT, $result);
    }

    public function testShouldLogCriticalAndRejectMessageIfJobIsNotRoot()
    {
        $job = new Job();
        $job->setRootJob(new Job());

        $jobStorage = $this->createJobStorageMock();
        $jobStorage
            ->expects($this->once())
            ->method('findJobById')
            ->with(12345)
            ->will($this->returnValue($job))
        ;

        $producer = $this->createMessageProducerMock();

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('[DependentJobMessageProcessor] Expected root job but got child. id: "12345"')
        ;

        $message = new NullMessage();
        $message->setBody(json_encode(['jobId' => 12345]));

        $processor = new DependentJobMessageProcessor($jobStorage, $producer, $logger);

        $result = $processor->process($message, $this->createContextMock());

        $this->assertEquals(Result::REJECT, $result);
    }

    public function testShouldDoNothingIfDependentJobsAreMissing()
    {
        $job = new Job();

        $jobStorage = $this->createJobStorageMock();
        $jobStorage
            ->expects($this->once())
            ->method('findJobById')
            ->with(12345)
            ->will($this->returnValue($job))
        ;

        $producer = $this->createMessageProducerMock();
        $producer
            ->expects($this->never())
            ->method('send')
        ;

        $logger = $this->createLoggerMock();

        $message = new NullMessage();
        $message->setBody(json_encode(['jobId' => 12345]));

        $processor = new DependentJobMessageProcessor($jobStorage, $producer, $logger);

        $result = $processor->process($message, $this->createContextMock());

        $this->assertEquals(Result::ACK, $result);
    }

    public function testShouldLogCriticalAndRejectMessageIfDependentJobTopicIsMissing()
    {
        $job = new Job();
        $job->setId(123);
        $job->setData([
            'dependentJobs' => [
                [],
            ],
        ]);

        $jobStorage = $this->createJobStorageMock();
        $jobStorage
            ->expects($this->once())
            ->method('findJobById')
            ->with(12345)
            ->will($this->returnValue($job))
        ;

        $producer = $this->createMessageProducerMock();
        $producer
            ->expects($this->never())
            ->method('send')
        ;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('[DependentJobMessageProcessor] Got invalid dependent job data. job: "123", dependentJob: "[]"')
        ;

        $message = new NullMessage();
        $message->setBody(json_encode(['jobId' => 12345]));

        $processor = new DependentJobMessageProcessor($jobStorage, $producer, $logger);

        $result = $processor->process($message, $this->createContextMock());

        $this->assertEquals(Result::REJECT, $result);
    }

    public function testShouldLogCriticalAndRejectMessageIfDependentJobMessageIsMissing()
    {
        $job = new Job();
        $job->setId(123);
        $job->setData([
            'dependentJobs' => [
                [
                    'topic' => 'topic-name',
                ],
            ],
        ]);

        $jobStorage = $this->createJobStorageMock();
        $jobStorage
            ->expects($this->once())
            ->method('findJobById')
            ->with(12345)
            ->will($this->returnValue($job))
        ;

        $producer = $this->createMessageProducerMock();
        $producer
            ->expects($this->never())
            ->method('send')
        ;

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('[DependentJobMessageProcessor] Got invalid dependent job data. '.
             'job: "123", dependentJob: "{"topic":"topic-name"}"')
        ;

        $message = new NullMessage();
        $message->setBody(json_encode(['jobId' => 12345]));

        $processor = new DependentJobMessageProcessor($jobStorage, $producer, $logger);

        $result = $processor->process($message, $this->createContextMock());

        $this->assertEquals(Result::REJECT, $result);
    }

    public function testShouldPublishDependentMessage()
    {
        $job = new Job();
        $job->setId(123);
        $job->setData([
            'dependentJobs' => [
                [
                    'topic' => 'topic-name',
                    'message' => 'message',
                ],
            ],
        ]);

        $jobStorage = $this->createJobStorageMock();
        $jobStorage
            ->expects($this->once())
            ->method('findJobById')
            ->with(12345)
            ->will($this->returnValue($job))
        ;

        $expectedMessage = null;
        $producer = $this->createMessageProducerMock();
        $producer
            ->expects($this->once())
            ->method('send')
            ->with('topic-name', $this->isInstanceOf(Message::class))
            ->will($this->returnCallback(function ($topic, Message $message) use (&$expectedMessage) {
                $expectedMessage = $message;
            }))
        ;

        $logger = $this->createLoggerMock();

        $message = new NullMessage();
        $message->setBody(json_encode(['jobId' => 12345]));

        $processor = new DependentJobMessageProcessor($jobStorage, $producer, $logger);

        $result = $processor->process($message, $this->createContextMock());

        $this->assertEquals(Result::ACK, $result);

        $this->assertEquals('message', $expectedMessage->getBody());
        $this->assertNull($expectedMessage->getPriority());
    }

    public function testShouldPublishDependentMessageWithPriority()
    {
        $job = new Job();
        $job->setId(123);
        $job->setData([
            'dependentJobs' => [
                [
                    'topic' => 'topic-name',
                    'message' => 'message',
                    'priority' => 'priority',
                ],
            ],
        ]);

        $jobStorage = $this->createJobStorageMock();
        $jobStorage
            ->expects($this->once())
            ->method('findJobById')
            ->with(12345)
            ->will($this->returnValue($job))
        ;

        $expectedMessage = null;
        $producer = $this->createMessageProducerMock();
        $producer
            ->expects($this->once())
            ->method('send')
            ->with('topic-name', $this->isInstanceOf(Message::class))
            ->will($this->returnCallback(function ($topic, Message $message) use (&$expectedMessage) {
                $expectedMessage = $message;
            }))
        ;

        $logger = $this->createLoggerMock();

        $message = new NullMessage();
        $message->setBody(json_encode(['jobId' => 12345]));

        $processor = new DependentJobMessageProcessor($jobStorage, $producer, $logger);

        $result = $processor->process($message, $this->createContextMock());

        $this->assertEquals(Result::ACK, $result);

        $this->assertEquals('message', $expectedMessage->getBody());
        $this->assertEquals('priority', $expectedMessage->getPriority());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSContext
     */
    private function createContextMock()
    {
        return $this->createMock(JMSContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JobStorage
     */
    private function createJobStorageMock()
    {
        return $this->createMock(JobStorage::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageProducerInterface
     */
    private function createMessageProducerMock()
    {
        return $this->createMock(MessageProducerInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }
}
