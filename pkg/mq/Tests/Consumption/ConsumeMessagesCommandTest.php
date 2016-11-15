<?php
namespace Formapro\MessageQueue\Tests\Consumption;

use Formapro\Jms\JMSContext;
use Formapro\Jms\Queue;
use Formapro\MessageQueue\Consumption\ChainExtension;
use Formapro\MessageQueue\Consumption\ConsumeMessagesCommand;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;

class ConsumeMessagesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithRequiredAttributes()
    {
        new ConsumeMessagesCommand($this->createQueueConsumerMock());
    }

    public function testShouldHaveCommandName()
    {
        $command = new ConsumeMessagesCommand($this->createQueueConsumerMock());

        $this->assertEquals('formapro:message-queue:transport:consume', $command->getName());
    }

    public function testShouldHaveExpectedOptions()
    {
        $command = new ConsumeMessagesCommand($this->createQueueConsumerMock());

        $options = $command->getDefinition()->getOptions();

        $this->assertCount(3, $options);
        $this->assertArrayHasKey('memory-limit', $options);
        $this->assertArrayHasKey('message-limit', $options);
        $this->assertArrayHasKey('time-limit', $options);
    }

    public function testShouldHaveExpectedAttributes()
    {
        $command = new ConsumeMessagesCommand($this->createQueueConsumerMock());

        $arguments = $command->getDefinition()->getArguments();

        $this->assertCount(2, $arguments);
        $this->assertArrayHasKey('processor-service', $arguments);
        $this->assertArrayHasKey('queue', $arguments);
    }

    public function testShouldThrowExceptionIfProcessorInstanceHasWrongClass()
    {
        $this->setExpectedException(\LogicException::class, 'Invalid message processor service given.'.
            ' It must be an instance of Formapro\MessageQueue\Consumption\MessageProcessorInterface but stdClass');

        $container = new Container();
        $container->set('processor-service', new \stdClass());

        $command = new ConsumeMessagesCommand($this->createQueueConsumerMock());
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([
            'queue' => 'queue-name',
            'processor-service' => 'processor-service',
        ]);
    }

    public function testShouldExecuteConsumption()
    {
        $processor = $this->createMessageProcessor();

        $queue = $this->createQueueMock();

        $context = $this->createContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->willReturn($queue)
        ;
        $context
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
            ->expects($this->exactly(2))
            ->method('getContext')
            ->will($this->returnValue($context))
        ;

        $container = new Container();
        $container->set('processor-service', $processor);

        $command = new ConsumeMessagesCommand($consumer);
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([
            'queue' => 'queue-name',
            'processor-service' => 'processor-service',
        ]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSContext
     */
    protected function createContextMock()
    {
        return $this->createMock(JMSContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Queue
     */
    protected function createQueueMock()
    {
        return $this->createMock(Queue::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MessageProcessorInterface
     */
    protected function createMessageProcessor()
    {
        return $this->createMock(MessageProcessorInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|QueueConsumer
     */
    protected function createQueueConsumerMock()
    {
        return $this->createMock(QueueConsumer::class);
    }
}
