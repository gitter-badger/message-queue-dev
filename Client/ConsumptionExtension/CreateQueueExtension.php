<?php

namespace FormaPro\MessageQueue\Client\ConsumptionExtension;

use FormaPro\MessageQueue\Consumption\AbstractExtension;
use FormaPro\MessageQueue\Consumption\Context;
use FormaPro\MessageQueue\Client\DriverInterface;

class CreateQueueExtension extends AbstractExtension
{
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
        if (isset($this->createdQueues[$context->getQueueName()])) {
            return;
        }

        $this->createdQueues[$context->getQueueName()] = true;
        
        $this->driver->createQueue($context->getQueueName());

        $context->getLogger()->debug(sprintf(
            '[CreateQueueExtension] Make sure the queue %s exists on a broker side.',
            $context->getQueueName()
        ));
    }
}
