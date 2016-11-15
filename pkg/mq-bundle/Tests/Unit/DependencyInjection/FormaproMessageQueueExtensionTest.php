<?php
namespace Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection;

use Formapro\MessageQueue\Client\MessageProducer;
use Formapro\MessageQueue\Client\NullDriver;
use Formapro\MessageQueue\Client\TraceableMessageProducer;
use Formapro\MessageQueue\DependencyInjection\DefaultTransportFactory;
use Formapro\MessageQueue\DependencyInjection\NullTransportFactory;
use Formapro\MessageQueue\Transport\Null\NullContext;
use Formapro\MessageQueueBundle\DependencyInjection\Configuration;
use Formapro\MessageQueueBundle\DependencyInjection\FormaproMessageQueueExtension;
use Formapro\MessageQueueBundle\Test\ClassExtensionTrait;
use Formapro\MessageQueueBundle\Tests\Unit\Mocks\FooTransportFactory;
use Formapro\MessageQueueDbalTransport\Client\DbalDriver;
use Formapro\MessageQueueDbalTransport\Transport\DbalConnection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FormaproMessageQueueExtensionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementConfigurationInterface()
    {
        self::assertClassExtends(Extension::class, FormaproMessageQueueExtension::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new FormaproMessageQueueExtension();
    }

    public function testThrowIfTransportFactoryNameEmpty()
    {
        $extension = new FormaproMessageQueueExtension();

        $this->setExpectedException(\LogicException::class, 'Transport factory name cannot be empty');
        $extension->addTransportFactory(new FooTransportFactory(null));
    }

    public function testThrowIfTransportFactoryWithSameNameAlreadyAdded()
    {
        $extension = new FormaproMessageQueueExtension();

        $extension->addTransportFactory(new FooTransportFactory('foo'));

        $this->setExpectedException(\LogicException::class, 'Transport factory with such name already added. Name foo');
        $extension->addTransportFactory(new FooTransportFactory('foo'));
    }

    public function testShouldConfigureNullTransport()
    {
        $container = new ContainerBuilder();

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new NullTransportFactory());

        $extension->load([[
            'transport' => [
                'null' => true,
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('formapro_message_queue.transport.null.context'));
        $context = $container->getDefinition('formapro_message_queue.transport.null.context');
        self::assertEquals(NullContext::class, $context->getClass());
    }

    public function testShouldUseNullTransportAsDefault()
    {
        $container = new ContainerBuilder();

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new NullTransportFactory());
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'transport' => [
                'default' => 'null',
                'null' => true,
            ],
        ]], $container);

        self::assertEquals(
            'formapro_message_queue.transport.default.context',
            (string) $container->getAlias('formapro_message_queue.transport.context')
        );
        self::assertEquals(
            'formapro_message_queue.transport.null.context',
            (string) $container->getAlias('formapro_message_queue.transport.default.context')
        );
    }

    public function testShouldConfigureFooTransport()
    {
        $container = new ContainerBuilder();

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new FooTransportFactory());

        $extension->load([[
            'transport' => [
                'foo' => ['foo_param' => 'aParam'],
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('foo.context'));
        $context = $container->getDefinition('foo.context');
        self::assertEquals(\stdClass::class, $context->getClass());
        self::assertEquals([['foo_param' => 'aParam']], $context->getArguments());
    }

    public function testShouldUseFooTransportAsDefault()
    {
        $container = new ContainerBuilder();

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new FooTransportFactory());
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'transport' => [
                'default' => 'foo',
                'foo' => ['foo_param' => 'aParam'],
            ],
        ]], $container);

        self::assertEquals(
            'formapro_message_queue.transport.default.context',
            (string) $container->getAlias('formapro_message_queue.transport.context')
        );
        self::assertEquals(
            'formapro_message_queue.transport.foo.context',
            (string) $container->getAlias('formapro_message_queue.transport.default.context')
        );
    }

    public function testShouldLoadClientServicesWhenEnabled()
    {
        $container = new ContainerBuilder();

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'client' => null,
            'transport' => [
                'default' => 'foo',
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('formapro_message_queue.client.config'));
        self::assertTrue($container->hasDefinition('formapro_message_queue.client.message_producer'));
    }

    public function testShouldUseMessageProducerByDefault()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'client' => null,
            'transport' => [
                'default' => 'foo',
            ],
        ]], $container);

        $messageProducer = $container->getDefinition('formapro_message_queue.client.message_producer');
        self::assertEquals(MessageProducer::class, $messageProducer->getClass());
    }

    public function testShouldUseMessageProducerIfTraceableProducerOptionSetToFalseExplicitly()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'client' => [
                'traceable_producer' => false,
            ],
            'transport' => [
                'default' => 'foo',
            ],
        ]], $container);

        $messageProducer = $container->getDefinition('formapro_message_queue.client.message_producer');
        self::assertEquals(MessageProducer::class, $messageProducer->getClass());
    }

    public function testShouldUseTraceableMessageProducerIfTraceableProducerOptionSetToTrueExplicitly()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'client' => [
                'traceable_producer' => true,
            ],
            'transport' => [
                'default' => 'foo',
            ],
        ]], $container);

        $messageProducer = $container->getDefinition('formapro_message_queue.client.traceable_message_producer');
        self::assertEquals(TraceableMessageProducer::class, $messageProducer->getClass());
        self::assertEquals(
            ['formapro_message_queue.client.message_producer', null, 0],
            $messageProducer->getDecoratedService()
        );

        self::assertInstanceOf(Reference::class, $messageProducer->getArgument(0));
        self::assertEquals(
            'formapro_message_queue.client.traceable_message_producer.inner',
            (string) $messageProducer->getArgument(0)
        );
    }

    public function testShouldConfigureDelayRedeliveredMessageExtension()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'client' => [
                'redelivered_delay_time' => 12345,
            ],
            'transport' => [
                'default' => 'foo',
            ],
        ]], $container);

        $extension = $container->getDefinition('formapro_message_queue.client.delay_redelivered_message_extension');
        self::assertEquals(12345, $extension->getArgument(1));
    }

    public function testShouldAddNullContextToNullDriverMapToDriverFactory()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'client' => true,
            'transport' => [
                'default' => 'foo',
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('formapro_message_queue.client.driver_factory'));
        $factory = $container->getDefinition('formapro_message_queue.client.driver_factory');

        $firstArgument = $factory->getArgument(0);
        self::assertArrayHasKey(NullContext::class, $firstArgument);
        self::assertEquals(NullDriver::class, $firstArgument[NullContext::class]);
    }

    public function testShouldAddDbalConnectionToDbalDriverMapToDriverFactory()
    {
        $this->markTestSkipped('Dbal transport is not ready');

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'client' => true,
            'transport' => [
                'default' => 'foo',
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('formapro_message_queue.client.driver_factory'));
        $factory = $container->getDefinition('formapro_message_queue.client.driver_factory');

        $firstArgument = $factory->getArgument(0);
        self::assertArrayHasKey(DbalConnection::class, $firstArgument);
        self::assertEquals(DbalDriver::class, $firstArgument[DbalConnection::class]);
    }

    public function testShouldAddDbalLazyConnectionToDbalDriverMapToDriverFactory()
    {
        $this->markTestSkipped('Dbal transport is not ready');

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaproMessageQueueExtension();
        $extension->addTransportFactory(new DefaultTransportFactory());

        $extension->load([[
            'client' => true,
            'transport' => [
                'default' => 'foo',
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('formapro_message_queue.client.driver_factory'));
        $factory = $container->getDefinition('formapro_message_queue.client.driver_factory');

        $firstArgument = $factory->getArgument(0);
        self::assertArrayHasKey(DbalConnection::class, $firstArgument);
        self::assertEquals(DbalDriver::class, $firstArgument[DbalConnection::class]);
    }

    public function testShouldLoadJobServicesIfEnabled()
    {
        $this->markTestSkipped('Jobs is not ready');

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaproMessageQueueExtension();

        $extension->load([[
            'transport' => [],
            'job' => true,
        ]], $container);

        self::assertTrue($container->hasDefinition('formapro_message_queue.job.runner'));
    }

    public function testShouldNotLoadJobServicesIfDisabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaproMessageQueueExtension();

        $extension->load([[
            'transport' => [],
            'job' => false,
        ]], $container);

        self::assertFalse($container->hasDefinition('formapro_message_queue.job.runner'));
    }

    public function testShouldAllowGetConfiguration()
    {
        $extension = new FormaproMessageQueueExtension();

        $configuration = $extension->getConfiguration([], new ContainerBuilder());

        self::assertInstanceOf(Configuration::class, $configuration);
    }

    public function testShouldLoadDoctrinePingConnectionExtensionServiceIfEnabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaProMessageQueueExtension();

        $extension->load([[
            'transport' => [],
            'doctrine' => [
                'ping_connection_extension' => true,
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('fp_message_queue.consumption.doctrine_ping_connection_extension'));
    }

    public function testShouldNotLoadDoctrinePingConnectionExtensionServiceIfDisabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaProMessageQueueExtension();

        $extension->load([[
            'transport' => [],
            'doctrine' => [
                'ping_connection_extension' => false,
            ],
        ]], $container);

        self::assertFalse($container->hasDefinition('fp_message_queue.consumption.doctrine_ping_connection_extension'));
    }

    public function testShouldLoadDoctrineClearIdentityMapExtensionServiceIfEnabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaProMessageQueueExtension();

        $extension->load([[
            'transport' => [],
            'doctrine' => [
                'clear_identity_map_extension' => true,
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('fp_message_queue.consumption.doctrine_clear_identity_map_extension'));
    }

    public function testShouldNotLoadDoctrineClearIdentityMapExtensionServiceIfDisabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $extension = new FormaProMessageQueueExtension();

        $extension->load([[
            'transport' => [],
            'doctrine' => [
                'clear_identity_map_extension' => false,
            ],
        ]], $container);

        self::assertFalse($container->hasDefinition('fp_message_queue.consumption.doctrine_clear_identity_map_extension'));
    }
}
