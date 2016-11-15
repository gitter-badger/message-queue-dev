<?php
namespace Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler;

use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildRouteRegistryPass;
use Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock\DestinationNameTopicSubscriber;
use Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock\InvalidTopicSubscriber;
use Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock\OnlyTopicNameTopicSubscriber;
use Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection\Compiler\Mock\ProcessorNameTopicSubscriber;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildRouteRegistryPassTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new BuildRouteRegistryPass();
    }

    public function testShouldBuildRouteRegistry()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(\stdClass::class);
        $processor->addTag('formapro_message_queue.client.message_processor', [
            'topicName' => 'topic',
            'processorName' => 'processor',
            'destinationName' => 'destination',
        ]);
        $container->setDefinition('processor', $processor);

        $router = new Definition();
        $router->setArguments([null, null, null]);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();
        $pass->process($container);

        $expectedRoutes = [
            'topic' => [
                ['processor', 'destination'],
            ],
        ];

        $this->assertEquals($expectedRoutes, $router->getArgument(2));
    }

    public function testThrowIfProcessorClassNameCouldNotBeFound()
    {
        $container = new ContainerBuilder();

        $processor = new Definition('notExistingClass');
        $processor->addTag('formapro_message_queue.client.message_processor', [
            'processorName' => 'processor',
        ]);
        $container->setDefinition('processor', $processor);

        $router = new Definition();
        $router->setArguments([null, []]);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The class "notExistingClass" could not be found.');
        $pass->process($container);
    }

    public function testShouldThrowExceptionIfTopicNameIsNotSet()
    {
        $this->setExpectedException(
            \LogicException::class,
            'Topic name is not set but it is required. service: "processor", '.
            'tag: "formapro_message_queue.client.message'
        );

        $container = new ContainerBuilder();

        $processor = new Definition(\stdClass::class);
        $processor->addTag('formapro_message_queue.client.message_processor');
        $container->setDefinition('processor', $processor);

        $router = new Definition();
        $router->setArguments([null, null, null]);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();
        $pass->process($container);
    }

    public function testShouldSetServiceIdAdProcessorIdIfIsNotSetInTag()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(\stdClass::class);
        $processor->addTag('formapro_message_queue.client.message_processor', [
            'topicName' => 'topic',
            'destinationName' => 'destination',
        ]);
        $container->setDefinition('processor-service-id', $processor);

        $router = new Definition();
        $router->setArguments([null, null, null]);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();
        $pass->process($container);

        $expectedRoutes = [
            'topic' => [
                ['processor-service-id', 'destination'],
            ],
        ];

        $this->assertEquals($expectedRoutes, $router->getArgument(2));
    }

    public function testShouldSetDefaultDestinationIfNotSetInTag()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(\stdClass::class);
        $processor->addTag('formapro_message_queue.client.message_processor', [
            'topicName' => 'topic',
        ]);
        $container->setDefinition('processor-service-id', $processor);

        $router = new Definition();
        $router->setArguments([null, null, null]);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();
        $pass->process($container);

        $expectedRoutes = [
            'topic' => [
                ['processor-service-id', Config::DEFAULT_QUEUE_NAME],
            ],
        ];

        $this->assertEquals($expectedRoutes, $router->getArgument(2));
    }

    public function testShouldBuildRouteFromSubscriberIfOnlyTopicNameSpecified()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(OnlyTopicNameTopicSubscriber::class);
        $processor->addTag('formapro_message_queue.client.message_processor');
        $container->setDefinition('processor-service-id', $processor);

        $router = new Definition();
        $router->setArguments([null, null, null]);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();
        $pass->process($container);

        $expectedRoutes = [
            'topic-subscriber-name' => [
                ['processor-service-id', Config::DEFAULT_QUEUE_NAME],
            ],
        ];

        $this->assertEquals($expectedRoutes, $router->getArgument(2));
    }

    public function testShouldBuildRouteFromSubscriberIfProcessorNameSpecified()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(ProcessorNameTopicSubscriber::class);
        $processor->addTag('formapro_message_queue.client.message_processor');
        $container->setDefinition('processor-service-id', $processor);

        $router = new Definition();
        $router->setArguments([null, null, null]);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();
        $pass->process($container);

        $expectedRoutes = [
            'topic-subscriber-name' => [
                ['subscriber-processor-name', Config::DEFAULT_QUEUE_NAME],
            ],
        ];

        $this->assertEquals($expectedRoutes, $router->getArgument(2));
    }

    public function testShouldBuildRouteFromSubscriberIfDestinationNameSpecified()
    {
        $container = new ContainerBuilder();

        $processor = new Definition(DestinationNameTopicSubscriber::class);
        $processor->addTag('formapro_message_queue.client.message_processor');
        $container->setDefinition('processor-service-id', $processor);

        $router = new Definition();
        $router->setArguments([null, null, null]);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();
        $pass->process($container);

        $expectedRoutes = [
            'topic-subscriber-name' => [
                ['processor-service-id', 'subscriber-destination-name'],
            ],
        ];

        $this->assertEquals($expectedRoutes, $router->getArgument(2));
    }

    public function testShouldThrowExceptionWhenTopicSubscriberConfigurationIsInvalid()
    {
        $this->setExpectedException(\LogicException::class, 'Topic subscriber configuration is invalid. "[12345]"');

        $container = new ContainerBuilder();

        $processor = new Definition(InvalidTopicSubscriber::class);
        $processor->addTag('formapro_message_queue.client.message_processor');
        $container->setDefinition('processor-service-id', $processor);

        $router = new Definition();
        $router->setArguments(['', '']);
        $container->setDefinition('formapro_message_queue.client.router', $router);

        $pass = new BuildRouteRegistryPass();
        $pass->process($container);
    }
}
