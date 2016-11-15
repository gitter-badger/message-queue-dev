<?php
namespace Formapro\MessageQueue\DependencyInjection;

use Formapro\MessageQueue\Transport\Null\NullContext;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class NullTransportFactory implements TransportFactoryInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name = 'null')
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createService(ContainerBuilder $container, array $config)
    {
        $contextId = sprintf('formapro_message_queue.transport.%s.context', $this->getName());
        $context = new Definition(NullContext::class);

        $container->setDefinition($contextId, $context);

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
