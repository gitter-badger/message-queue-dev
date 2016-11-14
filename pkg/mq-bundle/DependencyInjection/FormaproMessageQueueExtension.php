<?php

namespace Formapro\MessageQueueBundle\DependencyInjection;

use Formapro\MessageQueue\Client\NullDriver;
use Formapro\MessageQueue\Client\TraceableMessageProducer;
use Formapro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use Formapro\MessageQueue\Transport\Null\NullContext;
use Formapro\MessageQueueDbalTransport\Client\DbalDriver;
use Formapro\MessageQueueDbalTransport\Transport\DbalConnection;
use Formapro\MessageQueueDbalTransport\Transport\DbalLazyConnection;
use Formapro\MessageQueueJob\Job\Job;
use Formapro\Stomp\Client\StompDriver;
use Formapro\Stomp\Transport\StompContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FormaproMessageQueueExtension extends Extension
{
    /**
     * @var TransportFactoryInterface[]
     */
    private $factories;

    public function __construct()
    {
        $this->factories = [];
    }

    /**
     * @param TransportFactoryInterface $transportFactory
     */
    public function addTransportFactory(TransportFactoryInterface $transportFactory)
    {
        $name = $transportFactory->getName();

        if (empty($name)) {
            throw new \LogicException('Transport factory name cannot be empty');
        }
        if (array_key_exists($name, $this->factories)) {
            throw new \LogicException(sprintf('Transport factory with such name already added. Name %s', $name));
        }

        $this->factories[$name] = $transportFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration($this->factories), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        foreach ($config['transport'] as $name => $transportConfig) {
            $this->factories[$name]->createService($container, $transportConfig);
        }

        if (isset($config['client'])) {
            $loader->load('client.yml');

            $drivers = [
                NullContext::class => NullDriver::class,
            ];

            if (class_exists(DbalConnection::class)) {
                $drivers[DbalConnection::class] = DbalDriver::class;
                $drivers[DbalLazyConnection::class] = DbalDriver::class;
            }

            if (class_exists(StompContext::class)) {
                $drivers[StompContext::class] = StompDriver::class;
            }

            $driverFactory = $container->getDefinition('formapro_message_queue.client.driver_factory');
            $driverFactory->replaceArgument(0, $drivers);

            $configDef = $container->getDefinition('formapro_message_queue.client.config');
            $configDef->setArguments([
                $config['client']['prefix'],
                $config['client']['router_processor'],
                $config['client']['router_destination'],
                $config['client']['default_destination'],
            ]);

            if (false == empty($config['client']['traceable_producer'])) {
                $producerId = 'formapro_message_queue.client.traceable_message_producer';
                $container->register($producerId, TraceableMessageProducer::class)
                    ->setDecoratedService('formapro_message_queue.client.message_producer')
                    ->addArgument(new Reference('formapro_message_queue.client.traceable_message_producer.inner'))
                ;
            }

            $delayRedeliveredExtension = $container->getDefinition(
                'formapro_message_queue.client.delay_redelivered_message_extension'
            );
            $delayRedeliveredExtension->replaceArgument(1, $config['client']['redelivered_delay_time']);
        }

        if ($config['job']) {
            if (false == class_exists(Job::class)) {
                throw new \LogicException('Seems "fp/message-queue-job" is not installed. Please fix this issue.');
            }

            $loader->load('job.yml');
        }

        if ($config['doctrine']['ping_connection_extension']) {
            $loader->load('doctrine_ping_connection_extension.yml');
        }

        if ($config['doctrine']['clear_identity_map_extension']) {
            $loader->load('doctrine_clear_identity_map_extension.yml');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $rc = new \ReflectionClass(Configuration::class);

        $container->addResource(new FileResource($rc->getFileName()));

        return new Configuration($this->factories);
    }
}
