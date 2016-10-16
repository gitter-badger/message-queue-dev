<?php
namespace Formapro\MessageQueue\Consumption\Extension;

use Formapro\MessageQueue\Consumption\AbstractExtension;
use Formapro\MessageQueue\Consumption\Context;
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
}
