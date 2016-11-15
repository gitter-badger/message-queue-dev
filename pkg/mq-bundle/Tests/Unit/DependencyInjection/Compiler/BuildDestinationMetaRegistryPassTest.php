<?php
namespace Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler;

use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildDestinationMetaRegistryPass;
use Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock\DestinationNameTopicSubscriber;
use Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock\OnlyTopicNameTopicSubscriber;
use Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock\ProcessorNameTopicSubscriber;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildDestinationMetaRegistryPassTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new BuildDestinationMetaRegistryPass();
    }

    public function testShouldDoNothingIfRegistryServicesNotSetToContainer()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(\stdClass::class);
        $processor->addTag('formapro_message_queue.client.message_processor', [
            'processorName' => 'processor',
        ]);
        $container->setDefinition('processor', $processor);

        $pass = new BuildDestinationMetaRegistryPass();
        $pass->process($container);
    }

    public function testThrowIfProcessorClassNameCouldNotBeFound()
    {
        $container = new ContainerBuilder();

        $processor = new Definition('notExistingClass');
        $processor->addTag('formapro_message_queue.client.message_processor', [
            'processorName' => 'processor',
        ]);
        $container->setDefinition('processor', $processor);

        $registry = new Definition();
        $registry->setArguments([null, []]);
        $container->setDefinition('formapro_message_queue.client.meta.destination_meta_registry', $registry);

        $pass = new BuildDestinationMetaRegistryPass();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The class "notExistingClass" could not be found.');
        $pass->process($container);
    }

    public function testShouldBuildDestinationMetaRegistry()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(\stdClass::class);
        $processor->addTag('formapro_message_queue.client.message_processor', [
            'processorName' => 'processor',
        ]);
        $container->setDefinition('processor', $processor);

        $registry = new Definition();
        $registry->setArguments([null, []]);
        $container->setDefinition('formapro_message_queue.client.meta.destination_meta_registry', $registry);

        $pass = new BuildDestinationMetaRegistryPass();
        $pass->process($container);

        $expectedDestinations = [
            Config::DEFAULT_QUEUE_NAME => ['subscribers' => ['processor']],
        ];

        $this->assertEquals($expectedDestinations, $registry->getArgument(1));
    }

    public function testShouldSetServiceIdAdProcessorIdIfIsNotSetInTag()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(\stdClass::class);
        $processor->addTag('formapro_message_queue.client.message_processor', []);
        $container->setDefinition('processor-service-id', $processor);

        $registry = new Definition();
        $registry->setArguments([null, []]);
        $container->setDefinition('formapro_message_queue.client.meta.destination_meta_registry', $registry);

        $pass = new BuildDestinationMetaRegistryPass();
        $pass->process($container);

        $expectedDestinations = [
            Config::DEFAULT_QUEUE_NAME => ['subscribers' => ['processor-service-id']],
        ];

        $this->assertEquals($expectedDestinations, $registry->getArgument(1));
    }

    public function testShouldSetDestinationTIfSetInTag()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(\stdClass::class);
        $processor->addTag('formapro_message_queue.client.message_processor', [
            'destinationName' => 'destination',
        ]);
        $container->setDefinition('processor-service-id', $processor);

        $registry = new Definition();
        $registry->setArguments([null, []]);
        $container->setDefinition('formapro_message_queue.client.meta.destination_meta_registry', $registry);

        $pass = new BuildDestinationMetaRegistryPass();
        $pass->process($container);

        $expectedDestinations = [
            'destination' => ['subscribers' => ['processor-service-id']],
        ];

        $this->assertEquals($expectedDestinations, $registry->getArgument(1));
    }

    public function testShouldBuildDestinationFromSubscriberIfOnlyTopicNameSpecified()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(OnlyTopicNameTopicSubscriber::class);
        $processor->addTag('formapro_message_queue.client.message_processor');
        $container->setDefinition('processor-service-id', $processor);

        $registry = new Definition();
        $registry->setArguments([null, []]);
        $container->setDefinition('formapro_message_queue.client.meta.destination_meta_registry', $registry);

        $pass = new BuildDestinationMetaRegistryPass();
        $pass->process($container);

        $expectedDestinations = [
            Config::DEFAULT_QUEUE_NAME => ['subscribers' => ['processor-service-id']],
        ];

        $this->assertEquals($expectedDestinations, $registry->getArgument(1));
    }

    public function testShouldBuildDestinationFromSubscriberIfProcessorNameSpecified()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(ProcessorNameTopicSubscriber::class);
        $processor->addTag('formapro_message_queue.client.message_processor');
        $container->setDefinition('processor-service-id', $processor);

        $registry = new Definition();
        $registry->setArguments([null, []]);
        $container->setDefinition('formapro_message_queue.client.meta.destination_meta_registry', $registry);

        $pass = new BuildDestinationMetaRegistryPass();
        $pass->process($container);

        $expectedDestinations = [
            Config::DEFAULT_QUEUE_NAME => ['subscribers' => ['subscriber-processor-name']],
        ];

        $this->assertEquals($expectedDestinations, $registry->getArgument(1));
    }

    public function testShouldBuildDestinationFromSubscriberIfDestinationNameSpecified()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(DestinationNameTopicSubscriber::class);
        $processor->addTag('formapro_message_queue.client.message_processor');
        $container->setDefinition('processor-service-id', $processor);

        $registry = new Definition();
        $registry->setArguments([null, []]);
        $container->setDefinition('formapro_message_queue.client.meta.destination_meta_registry', $registry);

        $pass = new BuildDestinationMetaRegistryPass();
        $pass->process($container);

        $expectedDestinations = [
            'subscriber-destination-name' => ['subscribers' => ['processor-service-id']],
        ];

        $this->assertEquals($expectedDestinations, $registry->getArgument(1));
    }
}
