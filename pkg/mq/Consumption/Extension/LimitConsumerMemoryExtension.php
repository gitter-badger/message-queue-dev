<?php
namespace Formapro\MessageQueue\Consumption\Extension;

use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;
use Formapro\MessageQueue\Consumption\ExtensionInterface;

class LimitConsumerMemoryExtension implements ExtensionInterface
{
    use EmptyExtensionTrait;

    /**
     * @var int
     */
    protected $memoryLimit;

    /**
     * @param int $memoryLimit Megabytes
     */
    public function __construct($memoryLimit)
    {
        if (false == is_int($memoryLimit)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected memory limit is int but got: "%s"',
                is_object($memoryLimit) ? get_class($memoryLimit) : gettype($memoryLimit)
            ));
        }

        $this->memoryLimit = $memoryLimit * 1024 * 1024;
    }

    /**
     * {@inheritdoc}
     */
    public function onBeforeReceive(Context $context)
    {
        $this->checkMemory($context);
    }

    /**
     * {@inheritdoc}
     */
    public function onPostReceived(Context $context)
    {
        $this->checkMemory($context);
    }

    /**
     * {@inheritdoc}
     */
    public function onIdle(Context $context)
    {
        $this->checkMemory($context);
    }

    /**
     * @param Context $context
     */
    protected function checkMemory(Context $context)
    {
        $memoryUsage = memory_get_usage(true);
        if ($memoryUsage >= $this->memoryLimit) {
            $context->getLogger()->debug(sprintf(
                '[LimitConsumerMemoryExtension] Interrupt execution as memory limit reached. limit: "%s", used: "%s"',
                $this->memoryLimit,
                $memoryUsage
            ));

            $context->setExecutionInterrupted(true);
        }
    }
}
