<?php
namespace Formapro\MessageQueue\Tests\DependencyInjection;

use Formapro\MessageQueue\DependencyInjection\DefaultTransportFactory;
use Formapro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultTransportFactoryTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementTransportFactoryInterface()
    {
        $this->assertClassImplements(TransportFactoryInterface::class, DefaultTransportFactory::class);
    }

    public function testCouldBeConstructedWithDefaultName()
    {
        $transport = new DefaultTransportFactory();

        $this->assertEquals('default', $transport->getName());
    }

    public function testCouldBeConstructedWithCustomName()
    {
        $transport = new DefaultTransportFactory('theCustomName');

        $this->assertEquals('theCustomName', $transport->getName());
    }

    public function testShouldAllowAddConfiguration()
    {
        $transport = new DefaultTransportFactory();
        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $transport->addConfiguration($rootNode);
        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), ['the_alias']);

        $this->assertEquals(['alias' => 'the_alias'], $config);
    }

    public function testShouldCreateService()
    {
        $container = new ContainerBuilder();

        $transport = new DefaultTransportFactory();

        $serviceId = $transport->createService($container, ['alias' => 'the_alias']);

        $this->assertEquals('formapro_message_queue.transport.default.context', $serviceId);

        $this->assertTrue($container->hasAlias($serviceId));
        $context = $container->getAlias($serviceId);
        $this->assertEquals('formapro_message_queue.transport.the_alias.context', (string) $context);

        $this->assertTrue($container->hasAlias('formapro_message_queue.transport.context'));
        $context = $container->getAlias('formapro_message_queue.transport.context');
        $this->assertEquals($serviceId, (string) $context);
    }
}
