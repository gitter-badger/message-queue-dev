<?php
namespace FormaPro\MessageQueue\Tests\Unit\DependencyInjection;

use Doctrine\DBAL\Connection;
use FormaPro\MessageQueue\Consumption\Dbal\Extension\RedeliverOrphanMessagesDbalExtension;
use FormaPro\MessageQueue\Consumption\Dbal\Extension\RejectMessageOnExceptionDbalExtension;
use FormaPro\MessageQueue\DependencyInjection\DbalTransportFactory;
use FormaPro\MessageQueue\DependencyInjection\TransportFactoryInterface;
use FormaPro\MessageQueue\Transport\Dbal\DbalLazyConnection;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DbalTransportFactoryTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementTransportFactoryInterface()
    {
        $this->assertClassImplements(TransportFactoryInterface::class, DbalTransportFactory::class);
    }

    public function testCouldBeConstructedWithDefaultName()
    {
        $transport = new DbalTransportFactory();

        $this->assertEquals('dbal', $transport->getName());
    }

    public function testCouldBeConstructedWithCustomName()
    {
        $transport = new DbalTransportFactory('theCustomName');

        $this->assertEquals('theCustomName', $transport->getName());
    }

    public function testShouldAllowAddConfiguration()
    {
        $transport = new DbalTransportFactory();
        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $transport->addConfiguration($rootNode);
        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), []);

        $this->assertEquals([
            'connection' => 'default',
            'table' => 'fp_message_queue',
            'orphan_time' => 300,
            'polling_interval' => 1000,
        ], $config);
    }

    public function testShouldCreateService()
    {
        $container = new ContainerBuilder();

        $transport = new DbalTransportFactory();

        $serviceId = $transport->createService($container, [
            'connection' => 'connection-name',
            'table' => 'table-name',
            'orphan_time' => 12345,
            'polling_interval' => 7890,
        ]);

        $this->assertEquals('fp_message_queue.transport.dbal.connection', $serviceId);
        $this->assertTrue($container->hasDefinition($serviceId));

        $this->assertTrue($container->hasDefinition('fp_message_queue.transport.dbal.connection'));
        $connection = $container->getDefinition('fp_message_queue.transport.dbal.connection');
        $this->assertEquals(DbalLazyConnection::class, $connection->getClass());
        $this->assertInstanceOf(Reference::class, $connection->getArgument(0));
        $this->assertEquals('doctrine', (string) $connection->getArgument(0));
        $this->assertEquals('connection-name', $connection->getArgument(1));
        $this->assertEquals('table-name', $connection->getArgument(2));
        $this->assertEquals(['polling_interval' => 7890], $connection->getArgument(3));
    }

    public function testShouldCreateDbalExtensions()
    {
        $container = new ContainerBuilder();

        $transport = new DbalTransportFactory();

        $serviceId = $transport->createService($container, [
            'connection' => 'connection-name',
            'table' => 'table-name',
            'orphan_time' => 12345,
            'polling_interval' => 7890,
        ]);

        //guard
        $this->assertEquals('fp_message_queue.transport.dbal.connection', $serviceId);
        $this->assertTrue($container->hasDefinition($serviceId));

        $this->assertTrue($container->hasDefinition(
            'fp_message_queue.consumption.dbal.redeliver_orphan_messages_extension'
        ));

        $orphanExtensionDef = $container->getDefinition(
            'fp_message_queue.consumption.dbal.redeliver_orphan_messages_extension'
        );
        $this->assertEquals(RedeliverOrphanMessagesDbalExtension::class, $orphanExtensionDef->getClass());
        $this->assertFalse($orphanExtensionDef->isPublic());
        $this->assertEquals([12345], $orphanExtensionDef->getArguments());

        $expectedTags = [
            'fp_message_queue.consumption.extension' => [
                [
                    'priority' => 20,
                ]
            ]
        ];

        $this->assertEquals($expectedTags, $orphanExtensionDef->getTags());

        $this->assertTrue($container->hasDefinition(
            'fp_message_queue.consumption.dbal.reject_message_on_exception_extension'
        ));

        $rejectOnExceptionExtensionDef = $container->getDefinition(
            'fp_message_queue.consumption.dbal.reject_message_on_exception_extension'
        );
        $this->assertEquals(RejectMessageOnExceptionDbalExtension::class, $rejectOnExceptionExtensionDef->getClass());
        $this->assertFalse($rejectOnExceptionExtensionDef->isPublic());
        $expectedTags = [
            'fp_message_queue.consumption.extension' => [[]]
        ];
        $this->assertEquals($expectedTags, $rejectOnExceptionExtensionDef->getTags());
    }
}
