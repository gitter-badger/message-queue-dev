<?php
namespace Formapro\MessageQueueBundle\Tests\Unit\DependencyInjection;

use Formapro\MessageQueue\DependencyInjection\DefaultTransportFactory;
use Formapro\MessageQueue\DependencyInjection\NullTransportFactory;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueueBundle\DependencyInjection\Configuration;
use Formapro\MessageQueueBundle\Tests\Unit\Mocks\FooTransportFactory;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementConfigurationInterface()
    {
        $this->assertClassImplements(ConfigurationInterface::class, Configuration::class);
    }

    public function testCouldBeConstructedWithFactoriesAsFirstArgument()
    {
        new Configuration([]);
    }

    public function testThrowIfTransportNotConfigured()
    {
        $this->setExpectedException(
            InvalidConfigurationException::class,
            'The child node "transport" at path "formapro_message_queue" must be configured.'
        );

        $configuration = new Configuration([]);

        $processor = new Processor();
        $processor->processConfiguration($configuration, [[]]);
    }

    public function testShouldInjectFooTransportFactoryConfig()
    {
        $configuration = new Configuration([new FooTransportFactory()]);

        $processor = new Processor();
        $processor->processConfiguration($configuration, [[
            'transport' => [
                'foo' => [
                    'foo_param' => 'aParam',
                ],
            ],
        ]]);
    }

    public function testThrowExceptionIfFooTransportConfigInvalid()
    {
        $configuration = new Configuration([new FooTransportFactory()]);

        $processor = new Processor();

        $this->setExpectedException(
            InvalidConfigurationException::class,
            'The path "formapro_message_queue.transport.foo.foo_param" cannot contain an empty value, but got null.'
        );

        $processor->processConfiguration($configuration, [[
            'transport' => [
                'foo' => [
                    'foo_param' => null,
                ],
            ],
        ]]);
    }

    public function testShouldAllowConfigureDefaultTransport()
    {
        $configuration = new Configuration([new DefaultTransportFactory()]);

        $processor = new Processor();
        $processor->processConfiguration($configuration, [[
            'transport' => [
                'default' => ['alias' => 'foo'],
            ],
        ]]);
    }

    public function testShouldAllowConfigureNullTransport()
    {
        $configuration = new Configuration([new NullTransportFactory()]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [
                'null' => true,
            ],
        ]]);

        $this->assertArraySubset([
            'transport' => [
                'null' => [],
            ],
        ], $config);
    }

    public function testShouldAllowConfigureSeveralTransportsSameTime()
    {
        $configuration = new Configuration([
            new NullTransportFactory(),
            new DefaultTransportFactory(),
            new FooTransportFactory(),
        ]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [
                'default' => 'foo',
                'null' => true,
                'foo' => ['foo_param' => 'aParam'],
            ],
        ]]);

        $this->assertArraySubset([
            'transport' => [
                'default' => ['alias' => 'foo'],
                'null' => [],
                'foo' => ['foo_param' => 'aParam'],
            ],
        ], $config);
    }

    public function testShouldSetDefaultConfigurationForClient()
    {
        $configuration = new Configuration([new DefaultTransportFactory()]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [
                'default' => ['alias' => 'foo'],
            ],
            'client' => null,
        ]]);

        $this->assertArraySubset([
            'transport' => [
                'default' => ['alias' => 'foo'],
            ],
            'client' => [
                'prefix' => 'formapro',
                'router_processor' => 'formapro_message_queue.client.route_message_processor',
                'router_destination' => 'default',
                'default_destination' => 'default',
                'traceable_producer' => false,
                'redelivered_delay_time' => 10,
            ],
        ], $config);
    }

    public function testThrowExceptionIfRouterDestinationIsEmpty()
    {
        $this->setExpectedException(
            InvalidConfigurationException::class,
            'The path "formapro_message_queue.client.router_destination" cannot contain an empty value, but got "".'
        );

        $configuration = new Configuration([new DefaultTransportFactory()]);

        $processor = new Processor();
        $processor->processConfiguration($configuration, [[
            'transport' => [
                'default' => ['alias' => 'foo'],
            ],
            'client' => [
                'router_destination' => '',
            ],
        ]]);
    }

    public function testShouldThrowExceptionIfDefaultDestinationIsEmpty()
    {
        $this->setExpectedException(
            InvalidConfigurationException::class,
            'The path "formapro_message_queue.client.default_destination" cannot contain an empty value, but got "".'
        );

        $configuration = new Configuration([new DefaultTransportFactory()]);

        $processor = new Processor();
        $processor->processConfiguration($configuration, [[
            'transport' => [
                'default' => ['alias' => 'foo'],
            ],
            'client' => [
                'default_destination' => '',
            ],
        ]]);
    }

    public function testJobShouldBeDisabledByDefault()
    {
        $configuration = new Configuration([]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [],
        ]]);

        $this->assertArraySubset([
            'job' => false,
        ], $config);
    }

    public function testCouldEnableJob()
    {
        $configuration = new Configuration([]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [],
            'job' => true,
        ]]);

        $this->assertArraySubset([
            'job' => true,
        ], $config);
    }

    public function testDoctrinePingConnectionExtensionShouldBeDisabledByDefault()
    {
        $configuration = new Configuration([]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [],
        ]]);

        $this->assertArraySubset([
            'doctrine' => [
                'ping_connection_extension' => false,
            ],
        ], $config);
    }

    public function testDoctrinePingConnectionExtensionCouldBeEnabled()
    {
        $configuration = new Configuration([]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [],
            'doctrine' => [
                'ping_connection_extension' => true,
            ],
        ]]);

        $this->assertArraySubset([
            'doctrine' => [
                'ping_connection_extension' => true,
            ],
        ], $config);
    }

    public function testDoctrineClearIdentityMapExtensionShouldBeDisabledByDefault()
    {
        $configuration = new Configuration([]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [],
        ]]);

        $this->assertArraySubset([
            'doctrine' => [
                'clear_identity_map_extension' => false,
            ],
        ], $config);
    }

    public function testDoctrineClearIdentityMapExtensionCouldBeEnabled()
    {
        $configuration = new Configuration([]);

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [[
            'transport' => [],
            'doctrine' => [
                'clear_identity_map_extension' => true,
            ],
        ]]);

        $this->assertArraySubset([
            'doctrine' => [
                'clear_identity_map_extension' => true,
            ],
        ], $config);
    }
}
