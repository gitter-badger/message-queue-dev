<?php
namespace Formapro\MessageQueue\Client;

use Formapro\Fms\Message as FMSMessage;
use Formapro\MessageQueue\Client\Meta\DestinationMetaRegistry;
use Formapro\MessageQueue\Router\Recipient;
use Formapro\MessageQueue\Router\RecipientListRouterInterface;

class Router implements RecipientListRouterInterface
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var array
     */
    protected $routes;
    /**
     * @var DestinationMetaRegistry
     */
    private $destinationMetaRegistry;

    /**
     * @param DriverInterface         $driver
     * @param DestinationMetaRegistry $destinationMetaRegistry
     * @param array                   $routes
     */
    public function __construct(
        DriverInterface $driver,
        DestinationMetaRegistry $destinationMetaRegistry,
        array $routes = []
    ) {
        $this->driver = $driver;
        $this->destinationMetaRegistry = $destinationMetaRegistry;
        $this->routes = $routes;
    }

    /**
     * @param string $topicName
     * @param string $processorName
     * @param string $queueName
     */
    public function addRoute($topicName, $processorName, $queueName)
    {
        if (empty($topicName)) {
            throw new \InvalidArgumentException('The topic name must not be empty');
        }

        if (empty($processorName)) {
            throw new \InvalidArgumentException('The processor name must not be empty');
        }

        if (empty($queueName)) {
            throw new \InvalidArgumentException('The queue name must not be empty');
        }

        if (false == array_key_exists($topicName, $this->routes)) {
            $this->routes[$topicName] = [];
        }

        $this->routes[$topicName][] = [$processorName, $queueName];
    }

    /**
     * @internal
     *
     * @param string $topicName
     *
     * @return array
     */
    public function getTopicSubscribers($topicName)
    {
        return array_key_exists($topicName, $this->routes) ? $this->routes[$topicName] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function route(FMSMessage $message)
    {
        $topicName = $message->getProperty(Config::PARAMETER_TOPIC_NAME);
        if (false == $topicName) {
            throw new \LogicException(sprintf(
                'Got message without required parameter: "%s"',
                Config::PARAMETER_TOPIC_NAME
            ));
        }

        if (array_key_exists($topicName, $this->routes)) {
            foreach ($this->routes[$topicName] as $route) {
                $recipient = $this->createRecipient(
                    $message,
                    $route[0],
                    $this->destinationMetaRegistry->getDestinationMeta($route[1])->getTransportName()
                );

                yield $recipient;
            }
        }
    }

    /**
     * @param FMSMessage $message
     * @param string     $processorName
     * @param string     $queueName
     *
     * @return Recipient
     */
    protected function createRecipient(FMSMessage $message, $processorName, $queueName)
    {
        $properties = $message->getProperties();
        $properties[Config::PARAMETER_PROCESSOR_NAME] = $processorName;
        $properties[Config::PARAMETER_QUEUE_NAME] = $queueName;

        $newMessage = clone $message;
        $newMessage->setProperties($properties);

        $queue = $this->driver->createQueue($queueName);

        return new Recipient($queue, $newMessage);
    }
}
