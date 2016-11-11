<?php
namespace Formapro\MessageQueue\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultTransportFactory implements TransportFactoryInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name = 'default')
    {
        $this->name = $name;
    }
    
    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->beforeNormalization()
                ->ifString()
                ->then(function ($v) {
                    return array('alias' => $v);
                })
            ->end()
            ->children()
                ->scalarNode('alias')->isRequired()->cannotBeEmpty()->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function createService(ContainerBuilder $container, array $config)
    {
        $contextId = sprintf('formapro_message_queue.transport.%s.context', $this->getName());
        $aliasId = sprintf('formapro_message_queue.transport.%s.context', $config['alias']);
        
        $container->setAlias($contextId, $aliasId);
        $container->setAlias('formapro_message_queue.transport.context', $contextId);

        return $contextId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
