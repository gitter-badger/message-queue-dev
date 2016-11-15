<?php
namespace Formapro\JobQueue;

use Formapro\Jms\JMSContext;
use Formapro\Jms\Message as JMSMessage;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Client\MessageProducerInterface;
use Formapro\MessageQueue\Client\TopicSubscriberInterface;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;

class DependentJobMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var JobStorage
     */
    private $jobStorage;

    /**
     * @var MessageProducerInterface
     */
    private $producer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param JobStorage               $jobStorage
     * @param MessageProducerInterface $producer
     * @param LoggerInterface          $logger
     */
    public function __construct(JobStorage $jobStorage, MessageProducerInterface $producer, LoggerInterface $logger)
    {
        $this->jobStorage = $jobStorage;
        $this->producer = $producer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(JMSMessage $message, JMSContext $context)
    {
        $data = JSON::decode($message->getBody());

        if (!isset($data['jobId'])) {
            $this->logger->critical(sprintf(
                '[DependentJobMessageProcessor] Got invalid message. body: "%s"',
                $message->getBody()
            ));

            return Result::REJECT;
        }

        $job = $this->jobStorage->findJobById($data['jobId']);
        if (!$job) {
            $this->logger->critical(sprintf(
                '[DependentJobMessageProcessor] Job was not found. id: "%s"',
                $data['jobId']
            ));

            return Result::REJECT;
        }

        if (!$job->isRoot()) {
            $this->logger->critical(sprintf(
                '[DependentJobMessageProcessor] Expected root job but got child. id: "%s"',
                $data['jobId']
            ));

            return Result::REJECT;
        }

        $jobData = $job->getData();

        if (!isset($jobData['dependentJobs'])) {
            return Result::ACK;
        }

        $dependentJobs = $jobData['dependentJobs'];

        foreach ($dependentJobs as $dependentJob) {
            if (!isset($dependentJob['topic']) || !isset($dependentJob['message'])) {
                $this->logger->critical(sprintf(
                    '[DependentJobMessageProcessor] Got invalid dependent job data. job: "%s", dependentJob: "%s"',
                    $job->getId(),
                    JSON::encode($dependentJob)
                ));

                return Result::REJECT;
            }
        }

        foreach ($dependentJobs as $dependentJob) {
            $message = new Message();
            $message->setBody($dependentJob['message']);

            if (isset($dependentJob['priority'])) {
                $message->setPriority($dependentJob['priority']);
            }

            $this->producer->send($dependentJob['topic'], $message);
        }

        return Result::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::ROOT_JOB_STOPPED];
    }
}