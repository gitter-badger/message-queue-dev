<?php
namespace Formapro\Stomp\Tests\Symfony;

use Formapro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\Stomp\BufferedStompClient;
use Formapro\Stomp\Symfony\StompTransportFactory;
use Stomp\Network\Connection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class StompTransportFactoryTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementTransportFactoryInterface()
    {
        $this->assertClassImplements(TransportFactoryInterface::class, StompTransportFactory::class);
    }

    public function testCouldBeConstructedWithDefaultName()
    {
        $transport = new StompTransportFactory();

        $this->assertEquals('stomp', $transport->getName());
    }

    public function testCouldBeConstructedWithCustomName()
    {
        $transport = new StompTransportFactory('theCustomName');

        $this->assertEquals('theCustomName', $transport->getName());
    }

    public function testShouldAllowAddConfiguration()
    {
        $transport = new StompTransportFactory();
        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $transport->addConfiguration($rootNode);
        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), []);

        $this->assertEquals([
            'uri' => 'tcp://localhost:61613',
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'sync' => true,
            'connection_timeout' => 1,
            'buffer_size' => 1000,
        ], $config);
    }

    public function testShouldCreateService()
    {
        $container = new ContainerBuilder();

        $transport = new StompTransportFactory();

        $serviceId = $transport->createService($container, [
            'uri' => 'tcp://localhost:61613',
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'sync' => true,
            'connection_timeout' => 1,
            'buffer_size' => 1000,
        ]);

        $this->assertEquals('formapro_message_queue.transport.stomp.context', $serviceId);
        $this->assertTrue($container->hasDefinition($serviceId));

        $context = $container->getDefinition('formapro_message_queue.transport.stomp.context');
        $this->assertInstanceOf(Reference::class, $context->getArgument(0));
        $this->assertEquals('formapro_message_queue.transport.stomp.client', (string) $context->getArgument(0));

        $this->assertTrue($container->hasDefinition('formapro_message_queue.transport.stomp.connection'));
        $connection = $container->getDefinition('formapro_message_queue.transport.stomp.connection');
        $this->assertEquals(Connection::class, $connection->getClass());

        $this->assertTrue($container->hasDefinition('formapro_message_queue.transport.stomp.client'));
        $client = $container->getDefinition('formapro_message_queue.transport.stomp.client');
        $this->assertEquals(BufferedStompClient::class, $client->getClass());
        $this->assertInstanceOf(Reference::class, $client->getArgument(0));
        $this->assertEquals('formapro_message_queue.transport.stomp.connection', (string) $client->getArgument(0));
        $this->assertEquals(1000, $client->getArgument(1));

        $expectedMethodCalls = [
            ['setLogin', ['guest', 'guest']],
            ['setVhostname', ['/']],
            ['setSync', [true]],
        ];

        $this->assertEquals($expectedMethodCalls, $client->getMethodCalls());
    }
}
