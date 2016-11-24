<?php
namespace Formapro\MessageQueue\Client\ConsumptionExtension;

use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;
use Formapro\MessageQueue\Consumption\ExtensionInterface;

class CreateQueueExtension implements ExtensionInterface
{
    use EmptyExtensionTrait;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var bool[]
     */
    private $createdQueues = [];

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param Context $context
     */
    public function onBeforeReceive(Context $context)
    {
        if (isset($this->createdQueues[$context->getFMSQueue()->getQueueName()])) {
            return;
        }

        $this->createdQueues[$context->getFMSQueue()->getQueueName()] = true;

        $this->driver->createQueue($context->getFMSQueue()->getQueueName());

        $context->getLogger()->debug(sprintf(
            '[CreateQueueExtension] Make sure the queue %s exists on a broker side.',
            $context->getFMSQueue()->getQueueName()
        ));
    }
}
