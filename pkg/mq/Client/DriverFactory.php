<?php
namespace Formapro\MessageQueue\Client;

use Formapro\Jms\JMSContext;
use Formapro\MessageQueue\Transport\ConnectionInterface;

class DriverFactory
{
    /**
     * @var string[]
     */
    private $contextToDriverMap;

    /**
     * @param array $contextToDriverMap The array must have next structure ['contextClass' => 'driverClass']
     */
    public function __construct(array $contextToDriverMap)
    {
        $this->contextToDriverMap = $contextToDriverMap;
    }

    /**
     * @param JMSContext $context
     * @param Config     $config
     *
     * @return DriverInterface
     */
    public function create(JMSContext $context, Config $config)
    {
        $contextClass = get_class($context);

        if (array_key_exists($contextClass, $this->contextToDriverMap)) {
            $driverClass = $this->contextToDriverMap[$contextClass];

            return new $driverClass($context, $config);
        } else {
            throw new \LogicException(sprintf(
                'Unexpected context instance: "%s", supported "%s"',
                get_class($context),
                implode('", "', array_keys($this->contextToDriverMap))
            ));
        }
    }
}
