<?php
namespace Formapro\MessageQueue\Client;

use Formapro\Fms\Context;
use Formapro\Fms\Message as FMSMessage;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;

class DelegateMessageProcessor implements MessageProcessorInterface
{
    /**
     * @var MessageProcessorRegistryInterface
     */
    protected $registry;

    /**
     * @param MessageProcessorRegistryInterface $registry
     */
    public function __construct(MessageProcessorRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function process(FMSMessage $message, Context $context)
    {
        $processorName = $message->getProperty(Config::PARAMETER_PROCESSOR_NAME);
        if (false == $processorName) {
            throw new \LogicException(sprintf(
                'Got message without required parameter: "%s"',
                Config::PARAMETER_PROCESSOR_NAME
            ));
        }

        return $this->registry->get($processorName)->process($message, $context);
    }
}
