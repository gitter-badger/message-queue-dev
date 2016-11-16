<?php
namespace Formapro\MessageQueueBundle;

use Formapro\MessageQueue\DependencyInjection\DefaultTransportFactory;
use Formapro\MessageQueue\DependencyInjection\NullTransportFactory;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildDestinationMetaRegistryPass;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildExtensionsPass;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildMessageProcessorRegistryPass;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildRouteRegistryPass;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildTopicMetaSubscribersPass;
use Formapro\MessageQueueBundle\DependencyInjection\FormaproMessageQueueExtension;
use Formapro\MessageQueueDbalTransport\DependencyInjection\DbalTransportFactory;
use Formapro\MessageQueueDbalTransport\Transport\DbalConnection;
use Formapro\Stomp\StompContext;
use Formapro\Stomp\Symfony\StompTransportFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FormaproMessageQueueBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new BuildExtensionsPass());
        $container->addCompilerPass(new BuildRouteRegistryPass());
        $container->addCompilerPass(new BuildMessageProcessorRegistryPass());
        $container->addCompilerPass(new BuildTopicMetaSubscribersPass());
        $container->addCompilerPass(new BuildDestinationMetaRegistryPass());

        /** @var FormaproMessageQueueExtension $extension */
        $extension = $container->getExtension('formapro_message_queue');
        $extension->addTransportFactory(new DefaultTransportFactory());
        $extension->addTransportFactory(new NullTransportFactory());

        if (class_exists(DbalConnection::class)) {
            $extension->addTransportFactory(new DbalTransportFactory());
        }

        if (class_exists(StompContext::class)) {
            $extension->addTransportFactory(new StompTransportFactory());
        }
    }
}
