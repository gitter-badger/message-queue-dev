<?php
namespace Formapro\MessageQueueStompTransport\Tests\DependencyInjection;

use Formapro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use Formapro\MessageQueueStompTransport\DependencyInjection\StompTransportFactory;
use Formapro\MessageQueueStompTransport\Test\ClassExtensionTrait;
use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
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

        $this->assertEquals('formapro_message_queue.transport.stomp.connection', $serviceId);
        $this->assertTrue($container->hasDefinition($serviceId));

        $connection = $container->getDefinition('formapro_message_queue.transport.stomp.connection');
        $this->assertInstanceOf(Reference::class, $connection->getArgument(0));
        $this->assertEquals('formapro_message_queue.transport.stomp.client', (string) $connection->getArgument(0));

        $this->assertTrue($container->hasDefinition('formapro_message_queue.transport.stomp.client'));
        $clientFactory = $container->getDefinition('formapro_message_queue.transport.stomp.client');
        $this->assertEquals(BufferedStompClient::class, $clientFactory->getClass());
        $this->assertEquals('tcp://localhost:61613', $clientFactory->getArgument(0));
        $this->assertEquals(1000, $clientFactory->getArgument(1));

        $expectedMethodCalls = [
            ['setLogin', ['guest', 'guest']],
            ['setVhostname', ['/']],
            ['setSync', [true]],
            ['setConnectionTimeout', [1]],
        ];

        $this->assertEquals($expectedMethodCalls, $clientFactory->getMethodCalls());
    }
}
