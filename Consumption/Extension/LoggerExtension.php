<?php
namespace Formapro\MessageQueue\Consumption\Extension;

use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Transport\MessageInterface;
use Psr\Log\LoggerInterface;

class LoggerExtension implements ExtensionInterface
{
    use EmptyExtensionTrait;

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
        if (false == $context->getResult() instanceof Result) {
            return;
        }

        /** @var $result Result */
        $result = $context->getResult();

        switch ($result->getStatus()) {
            case Result::REJECT:
            case Result::REQUEUE:
                if ($result->getReason()) {
                    $this->logger->error($result->getReason(), $this->messageToLogContext($context->getMessage()));
                }

                break;
            case Result::ACK:
                if ($result->getReason()) {
                    $this->logger->info($result->getReason(), $this->messageToLogContext($context->getMessage()));
                }

                break;
            default:
                throw new \LogicException(sprintf('Got unexpected message result. "%s"', $result->getStatus()));
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
