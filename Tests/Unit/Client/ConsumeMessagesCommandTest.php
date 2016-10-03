<?php
namespace FormaPro\MessageQueue\Tests\Unit\Client;

use FormaPro\MessageQueue\Client\Config;
use FormaPro\MessageQueue\Client\ConsumeMessagesCommand;
use FormaPro\MessageQueue\Client\DelegateMessageProcessor;
use FormaPro\MessageQueue\Client\Meta\DestinationMetaRegistry;
use FormaPro\MessageQueue\Consumption\ChainExtension;
use FormaPro\MessageQueue\Consumption\QueueConsumer;
use FormaPro\MessageQueue\Transport\ConnectionInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeMessagesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithRequiredAttributes()
    {
        new ConsumeMessagesCommand(
            $this->createQueueConsumerMock(),
            $this->createDelegateMessageProcessorMock(),
            $this->createDestinationMetaRegistry([])
        );
    }

    public function testShouldHaveCommandName()
    {
        $command = new ConsumeMessagesCommand(
            $this->createQueueConsumerMock(),
            $this->createDelegateMessageProcessorMock(),
            $this->createDestinationMetaRegistry([])
        );

        $this->assertEquals('fp:message-queue:consume', $command->getName());
    }

    public function testShouldHaveExpectedOptions()
    {
        $command = new ConsumeMessagesCommand(
            $this->createQueueConsumerMock(),
            $this->createDelegateMessageProcessorMock(),
            $this->createDestinationMetaRegistry([])
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
            $this->createDestinationMetaRegistry([])
        );

        $arguments = $command->getDefinition()->getArguments();

        $this->assertCount(1, $arguments);
        $this->assertArrayHasKey('clientDestinationName', $arguments);
    }

    public function testShouldExecuteConsumptionAndUseDefaultQueueName()
    {
        $processor = $this->createDelegateMessageProcessorMock();

        $connection = $this->createConnectionMock();
        $connection
            ->expects($this->once())
            ->method('close')
        ;

        $consumer = $this->createQueueConsumerMock();
        $consumer
            ->expects($this->once())
            ->method('bind')
            ->with('aprefixt.adefaultqueuename', $this->identicalTo($processor))
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

        $command = new ConsumeMessagesCommand($consumer, $processor, $destinationMetaRegistry);

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testShouldExecuteConsumptionAndUseCustomClientDestinationName()
    {
        $processor = $this->createDelegateMessageProcessorMock();

        $connection = $this->createConnectionMock();
        $connection
            ->expects($this->once())
            ->method('close')
        ;

        $consumer = $this->createQueueConsumerMock();
        $consumer
            ->expects($this->once())
            ->method('bind')
            ->with('aprefixt.non-default-queue', $this->identicalTo($processor))
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

        $command = new ConsumeMessagesCommand($consumer, $processor, $destinationMetaRegistry);

        $tester = new CommandTester($command);
        $tester->execute([
            'clientDestinationName' => 'non-default-queue'
        ]);
    }

    public function testShouldExecuteConsumptionAndUseCustomClientDestinationNameWithCustomQueueFromArgument()
    {
        $processor = $this->createDelegateMessageProcessorMock();

        $connection = $this->createConnectionMock();
        $connection
            ->expects($this->once())
            ->method('close')
        ;

        $consumer = $this->createQueueConsumerMock();
        $consumer
            ->expects($this->once())
            ->method('bind')
            ->with('non-default-transport-queue', $this->identicalTo($processor))
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

        $command = new ConsumeMessagesCommand($consumer, $processor, $destinationMetaRegistry);

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
    protected function createDestinationMetaRegistry(array $destinationNames)
    {
        $config = new Config('aPrefixt', 'aRouterMessageProcessorName', 'aRouterQueueName', 'aDefaultQueueName');

        return new DestinationMetaRegistry($config, $destinationNames, 'default');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ConnectionInterface
     */
    protected function createConnectionMock()
    {
        return $this->createMock(ConnectionInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DelegateMessageProcessor
     */
    protected function createDelegateMessageProcessorMock()
    {
        return $this->createMock(DelegateMessageProcessor::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|QueueConsumer
     */
    protected function createQueueConsumerMock()
    {
        return $this->createMock(QueueConsumer::class);
    }
}
