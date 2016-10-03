<?php
namespace FormaPro\MessageQueue\Client;

use FormaPro\MessageQueue\Consumption\MessageProcessorInterface;

interface MessageProcessorRegistryInterface
{
    /**
     * @param string $processorName
     *
     * @return MessageProcessorInterface
     */
    public function get($processorName);
}
