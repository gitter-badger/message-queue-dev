<?php
namespace FormaPro\MessageQueue\Consumption\Extension;

use FormaPro\MessageQueue\Consumption\AbstractExtension;
use FormaPro\MessageQueue\Consumption\Context;
use FormaPro\MessageQueue\Consumption\MessageStatus;
use FormaPro\MessageQueue\Transport\MessageInterface;
use Psr\Log\LoggerInterface;

class LoggerExtension extends AbstractExtension
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function onStart(Context $context)
    {
        $context->setLogger($this->logger);
        $this->logger->debug(sprintf('Set context\'s logger %s', get_class($this->logger)));
    }

    /**
     * {@inheritdoc}
     */
    public function onPostReceived(Context $context)
    {
        if (false == $context->getStatus() instanceof MessageStatus) {
            return;
        }

        /** @var $status MessageStatus */
        $status = $context->getStatus();

        switch ($status->getStatus()) {
            case MessageStatus::REJECT:
            case MessageStatus::REQUEUE:
                if ($status->getReason()) {
                    $this->logger->error($status->getReason(), $this->messageToLogContext($context->getMessage()));
                }

                break;
            case MessageStatus::ACK:
                if ($status->getReason()) {
                    $this->logger->info($status->getReason(), $this->messageToLogContext($context->getMessage()));
                }

                break;
            default:
                throw new \LogicException(sprintf('Got unexpected message status. "%s"', $status->getStatus()));
        }
    }

    /**
     * @param MessageInterface $message
     *
     * @return array
     */
    private function messageToLogContext(MessageInterface $message)
    {
        return [
            'body' => $message->getBody(),
            'headers' => $message->getHeaders(),
            'properties' => $message->getProperties(),
        ];
    }
}
