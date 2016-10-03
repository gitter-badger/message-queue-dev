<?php
namespace FormaPro\MessageQueue\DependencyInjection;

use FormaPro\MessageQueue\Consumption\Dbal\Extension\RedeliverOrphanMessagesDbalExtension;
use FormaPro\MessageQueue\Consumption\Dbal\Extension\RejectMessageOnExceptionDbalExtension;
use FormaPro\MessageQueue\Transport\Dbal\DbalLazyConnection;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DbalTransportFactory implements TransportFactoryInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name = 'dbal')
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('connection')->defaultValue('default')->cannotBeEmpty()->end()
                ->scalarNode('table')->defaultValue('fp_message_queue')->cannotBeEmpty()->end()
                ->integerNode('orphan_time')->min(30)->defaultValue(300)->cannotBeEmpty()->end()
                ->integerNode('polling_interval')->min(50)->defaultValue(1000)->cannotBeEmpty()->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function createService(ContainerBuilder $container, array $config)
    {
        $orphanExtension = new Definition(RedeliverOrphanMessagesDbalExtension::class);
        $orphanExtension->setPublic(false);
        $orphanExtension->addTag('fp_message_queue.consumption.extension', ['priority' => 20]);
        $orphanExtension->setArguments([
            $config['orphan_time'],
        ]);
        $container->setDefinition(
            sprintf('fp_message_queue.consumption.%s.redeliver_orphan_messages_extension', $this->name),
            $orphanExtension
        );

        $rejectOnExceptionExtension = new Definition(RejectMessageOnExceptionDbalExtension::class);
        $rejectOnExceptionExtension->setPublic(false);
        $rejectOnExceptionExtension->addTag('fp_message_queue.consumption.extension');
        $container->setDefinition(
            sprintf('fp_message_queue.consumption.%s.reject_message_on_exception_extension', $this->name),
            $rejectOnExceptionExtension
        );

        $options = [
            'polling_interval' => $config['polling_interval'],
        ];

        $connection = new Definition(DbalLazyConnection::class);
        $connection->setArguments([
            new Reference('doctrine'),
            $config['connection'],
            $config['table'],
            $options
        ]);

        $connectionId = sprintf('fp_message_queue.transport.%s.connection', $this->getName());
        $container->setDefinition($connectionId, $connection);

        return $connectionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
