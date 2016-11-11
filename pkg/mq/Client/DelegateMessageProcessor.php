<?php
namespace Formapro\MessageQueue\Client;

use Formapro\Jms\Message as JMSMessage;
use Formapro\Jms\JMSContext;
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
    public function process(JMSMessage $message, JMSContext $context)
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
