<?php
namespace Formapro\MessageQueueBundle\DependencyInjection;

use Formapro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var TransportFactoryInterface[]
     */
    private $factories;

    /**
     * @param TransportFactoryInterface[] $factories
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $rootNode = $tb->root('formapro_message_queue');

        $transportChildren = $rootNode->children()
            ->arrayNode('transport')->isRequired()->children();

        foreach ($this->factories as $factory) {
            $factory->addConfiguration(
                $transportChildren->arrayNode($factory->getName())
            );
        }

        $rootNode->children()
            ->arrayNode('client')->children()
                ->booleanNode('traceable_producer')->defaultFalse()->end()
                ->scalarNode('prefix')->defaultValue('formapro')->end()
                ->scalarNode('router_processor')
                    ->defaultValue('formapro_message_queue.client.route_message_processor')
                ->end()
                ->scalarNode('router_destination')->defaultValue('default')->cannotBeEmpty()->end()
                ->scalarNode('default_destination')->defaultValue('default')->cannotBeEmpty()->end()
                ->integerNode('redelivered_delay_time')->min(1)->defaultValue(10)->end()
            ->end()->end()
            ->booleanNode('job')->defaultFalse()->end()
            ->arrayNode('doctrine')->addDefaultsIfNotSet()->children()
                ->booleanNode('ping_connection_extension')->defaultFalse()->end()
                ->booleanNode('clear_identity_map_extension')->defaultFalse()->end()
            ->end()->end()
        ;

        return $tb;
    }
}
