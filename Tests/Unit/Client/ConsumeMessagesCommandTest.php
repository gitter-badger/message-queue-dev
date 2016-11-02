<?php
namespace Formapro\MessageQueue\Tests\Unit\Client;

use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\ConsumeMessagesCommand;
use Formapro\MessageQueue\Client\DelegateMessageProcessor;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Meta\DestinationMetaRegistry;
use Formapro\MessageQueue\Consumption\ChainExtension;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Formapro\MessageQueue\Transport\ConnectionInterface;
use Formapro\MessageQueue\Transport\QueueInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeMessagesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithRequiredAttributes()
    {
        new ConsumeMessagesCommand(
            $this->createQueueConsumerMock(),
            $this->createDelegateMessageProcessorMock(),
            $this->createDestinationMetaRegistry([]),
            $this->createDriverMock()
        );
    }

    public function testShouldHaveCommandName()
    {
        $command = new ConsumeMessagesCommand(
            $this->createQueueConsumerMock(),
            $this->createDelegateMessageProcessorMock(),
            $this->createDestinationMetaRegistry([]),
            $this->createDriverMock()
        );

        $this->assertEquals('formapro:message-queue:consume', $command->getName());
    }

    public function testShouldHaveExpectedOptions()
    {
        $command = new ConsumeMessagesCommand(
            $this->createQueueConsumerMock(),
            $this->createDelegateMessageProcessorMock(),
            $this->createDestinationMetaRegistry([]),
            $this->createDriverMock()
        );

        $options = $command->getDefinition()->getOptions();

        $this->assertCount(3, $options);
        $this->assertArrayHasKey('memory-limit', $options);
        $this->assertArrayHasKey('message-limit', $options);
        $this->assertArrayHasKey('time-limit', $options);
    }

    public function testShouldHaveExpectedAttributes()
    {
        $command = new ConsumeMessagesCommand(
            $this->createQueueConsumerMock(),
            $this->createDelegateMessageProcessorMock(),
            $this->createDestinationMetaRegistry([]),
            $this->createDriverMock()
        );

        $arguments = $command->getDefinition()->getArguments();

        $this->assertCount(1, $arguments);
        $this->assertArrayHasKey('clientDestinationName', $arguments);
    }

    public function testShouldExecuteConsumptionAndUseDefaultQueueName()
    {
        $processor = $this->createDelegateMessageProcessorMock();
        $queue = $this->createQueueMock();

        $connection = $this->createConnectionMock();
        $connection
            ->expects($this->once())
            ->method('close')
        ;

        $consumer = $this->createQueueConsumerMock();
        $consumer
            ->expects($this->once())
            ->method('bind')
            ->with($this->identicalTo($queue), $this->identicalTo($processor))
        ;
        $consumer
            ->expects($this->once())
            ->method('consume')
            ->with($this->isInstanceOf(ChainExtension::class))
        ;
        $consumer
            ->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection))
        ;

        $destinationMetaRegistry = $this->createDestinationMetaRegistry([
            'default' => [],
        ]);

        $driver = $this->createDriverMock();
        $driver
            ->expects($this->once())
            ->method('createQueue')
            ->willReturn($queue)
        ;

        $command = new ConsumeMessagesCommand($consumer, $processor, $destinationMetaRegistry, $driver);

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testShouldExecuteConsumptionAndUseCustomClientDestinationName()
    {
        $processor = $this->createDelegateMessageProcessorMock();
        $queue = $this->createQueueMock();

        $connection = $this->createConnectionMock();
        $connection
            ->expects($this->once())
            ->method('close')
        ;

        $consumer = $this->createQueueConsumerMock();
        $consumer
            ->expects($this->once())
            ->method('bind')
            ->with($this->identicalTo($queue), $this->identicalTo($processor))
        ;
        $consumer
            ->expects($this->once())
            ->method('consume')
            ->with($this->isInstanceOf(ChainExtension::class))
        ;
        $consumer
            ->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection))
        ;

        $destinationMetaRegistry = $this->createDestinationMetaRegistry([
            'non-default-queue' => []
        ]);

        $driver = $this->createDriverMock();
        $driver
            ->expects($this->once())
            ->method('createQueue')
            ->willReturn($queue)
        ;

        $command = new ConsumeMessagesCommand($consumer, $processor, $destinationMetaRegistry, $driver);

        $tester = new CommandTester($command);
        $tester->execute([
            'clientDestinationName' => 'non-default-queue'
        ]);
    }

    public function testShouldExecuteConsumptionAndUseCustomClientDestinationNameWithCustomQueueFromArgument()
    {
        $processor = $this->createDelegateMessageProcessorMock();
        $queue = $this->createQueueMock();

        $connection = $this->createConnectionMock();
        $connection
            ->expects($this->once())
            ->method('close')
        ;

        $consumer = $this->createQueueConsumerMock();
        $consumer
            ->expects($this->once())
            ->method('bind')
            ->with($this->identicalTo($queue), $this->identicalTo($processor))
        ;
        $consumer
            ->expects($this->once())
            ->method('consume')
            ->with($this->isInstanceOf(ChainExtension::class))
        ;
        $consumer
            ->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection))
        ;

        $destinationMetaRegistry = $this->createDestinationMetaRegistry([
            'default' => [],
            'non-default-queue' => ['transportName' => 'non-default-transport-queue']
        ]);

        $driver = $this->createDriverMock();
        $driver
            ->expects($this->once())
            ->method('createQueue')
            ->willReturn($queue)
        ;

        $command = new ConsumeMessagesCommand($consumer, $processor, $destinationMetaRegistry, $driver);

        $tester = new CommandTester($command);
        $tester->execute([
            'clientDestinationName' => 'non-default-queue'
        ]);
    }

    /**
     * @param array $destinationNames
     *
     * @return DestinationMetaRegistry
     */
    private function createDestinationMetaRegistry(array $destinationNames)
    {
        $config = new Config('aPrefixt', 'aRouterMessageProcessorName', 'aRouterQueueName', 'aDefaultQueueName');

        return new DestinationMetaRegistry($config, $destinationNames, 'default');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ConnectionInterface
     */
    private function createConnectionMock()
    {
        return $this->createMock(ConnectionInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DelegateMessageProcessor
     */
    private function createDelegateMessageProcessorMock()
    {
        return $this->createMock(DelegateMessageProcessor::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|QueueConsumer
     */
    private function createQueueConsumerMock()
    {
        return $this->createMock(QueueConsumer::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DriverInterface
     */
    private function createDriverMock()
    {
        return $this->createMock(DriverInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|QueueInterface
     */
    private function createQueueMock()
    {
        return $this->createMock(QueueInterface::class);
    }
}
