<?php
namespace Formapro\AmqpExt\Tests\Symfony;

use Formapro\AmqpExt\AmqpConnectionFactory;
use Formapro\AmqpExt\Symfony\AmqpTransportFactory;
use Formapro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AmqpTransportFactoryTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementTransportFactoryInterface()
    {
        $this->assertClassImplements(TransportFactoryInterface::class, AmqpTransportFactory::class);
    }

    public function testCouldBeConstructedWithDefaultName()
    {
        $transport = new AmqpTransportFactory();

        $this->assertEquals('amqp', $transport->getName());
    }

    public function testCouldBeConstructedWithCustomName()
    {
        $transport = new AmqpTransportFactory('theCustomName');

        $this->assertEquals('theCustomName', $transport->getName());
    }

    public function testShouldAllowAddConfiguration()
    {
        $transport = new AmqpTransportFactory();
        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $transport->addConfiguration($rootNode);
        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), []);

        $this->assertEquals([
            'host' => 'localhost',
            'port' => 5672,
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'persisted' => false,
        ], $config);
    }

    public function testShouldCreateService()
    {
        $container = new ContainerBuilder();

        $transport = new AmqpTransportFactory();

        $serviceId = $transport->createService($container, [
            'host' => 'localhost',
            'port' => 5672,
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'persisted' => false,
        ]);

        $this->assertEquals('formapro_message_queue.transport.amqp.context', $serviceId);
        $this->assertTrue($container->hasDefinition($serviceId));

        $context = $container->getDefinition('formapro_message_queue.transport.amqp.context');
        $this->assertInstanceOf(Reference::class, $context->getFactory()[0]);
        $this->assertEquals('formapro_message_queue.transport.amqp.connection_factory', (string) $context->getFactory()[0]);
        $this->assertEquals('createContext', $context->getFactory()[1]);

        $this->assertTrue($container->hasDefinition('formapro_message_queue.transport.amqp.connection_factory'));
        $factory = $container->getDefinition('formapro_message_queue.transport.amqp.connection_factory');
        $this->assertEquals(AmqpConnectionFactory::class, $factory->getClass());
        $this->assertSame([[
            'host' => 'localhost',
            'port' => 5672,
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'persisted' => false,
        ]], $factory->getArguments());
    }
}
