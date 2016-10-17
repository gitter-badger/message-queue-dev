<?php
namespace Formapro\MessageQueue\Tests\Unit\DependencyInjection;

use Formapro\MessageQueue\DependencyInjection\NullTransportFactory;
use Formapro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use Formapro\MessageQueue\Transport\Null\NullConnection;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NullTransportFactoryTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementTransportFactoryInterface()
    {
        $this->assertClassImplements(TransportFactoryInterface::class, NullTransportFactory::class);
    }

    public function testCouldBeConstructedWithDefaultName()
    {
        $transport = new NullTransportFactory();

        $this->assertEquals('null', $transport->getName());
    }

    public function testCouldBeConstructedWithCustomName()
    {
        $transport = new NullTransportFactory('theCustomName');

        $this->assertEquals('theCustomName', $transport->getName());
    }

    public function testShouldAllowAddConfiguration()
    {
        $transport = new NullTransportFactory();
        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $transport->addConfiguration($rootNode);
        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), [true]);

        $this->assertEquals([], $config);
    }

    public function testShouldCreateService()
    {
        $container = new ContainerBuilder();

        $transport = new NullTransportFactory();

        $serviceId = $transport->createService($container, []);

        $this->assertEquals('formapro_message_queue.transport.null.connection', $serviceId);
        $this->assertTrue($container->hasDefinition($serviceId));

        $connection = $container->getDefinition($serviceId);
        $this->assertEquals(NullConnection::class, $connection->getClass());
        $this->assertNull($connection->getFactory());
    }
}
