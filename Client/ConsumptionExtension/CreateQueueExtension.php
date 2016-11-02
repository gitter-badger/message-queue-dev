<?php

namespace Formapro\MessageQueue\Client\ConsumptionExtension;

use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;

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
        if (isset($this->createdQueues[$context->getQueue()->getQueueName()])) {
            return;
        }

        $this->createdQueues[$context->getQueue()->getQueueName()] = true;

        $this->driver->createQueue($context->getQueue()->getQueueName());

        $context->getLogger()->debug(sprintf(
            '[CreateQueueExtension] Make sure the queue %s exists on a broker side.',
            $context->getQueue()->getQueueName()
        ));
    }
}
