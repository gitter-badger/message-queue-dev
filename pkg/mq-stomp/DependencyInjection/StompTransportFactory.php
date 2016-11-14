<?php
namespace Formapro\Stomp\DependencyInjection;

use Formapro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use Formapro\Stomp\Transport\BufferedStompClient;
use Formapro\Stomp\Transport\StompContext;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class StompTransportFactory implements TransportFactoryInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name = 'stomp')
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
                ->scalarNode('uri')->defaultValue('tcp://localhost:61613')->cannotBeEmpty()->end()
                ->scalarNode('login')->defaultValue('guest')->cannotBeEmpty()->end()
                ->scalarNode('password')->defaultValue('guest')->cannotBeEmpty()->end()
                ->scalarNode('vhost')->defaultValue('/')->cannotBeEmpty()->end()
                ->booleanNode('sync')->defaultTrue()->end()
                ->integerNode('connection_timeout')->min(1)->defaultValue(1)->end()
                ->integerNode('buffer_size')->min(1)->defaultValue(1000)->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function createService(ContainerBuilder $container, array $config)
    {
        $client = new Definition(BufferedStompClient::class);
        $client->setArguments([
            $config['uri'],
            $config['buffer_size'],
        ]);
        $client->addMethodCall('setLogin', [$config['login'], $config['password']]);
        $client->addMethodCall('setVhostname', [$config['vhost']]);
        $client->addMethodCall('setSync', [$config['sync']]);
        $client->addMethodCall('setConnectionTimeout', [$config['connection_timeout']]);

        $clientId = sprintf('formapro_message_queue.transport.%s.client', $this->getName());
        $container->setDefinition($clientId, $client);

        $context = new Definition(StompContext::class);
        $context->setArguments([new Reference($clientId)]);

        $contextId = sprintf('formapro_message_queue.transport.%s.context', $this->getName());
        $container->setDefinition($contextId, $context);

        return $contextId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
