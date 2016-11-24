<?php
namespace Formapro\MessageQueue\Tests\Consumption\Extension;

use Formapro\Fms\Consumer;
use Formapro\Fms\Context as FMSContext;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\Extension\LimitConsumerMemoryExtension;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Psr\Log\LoggerInterface;

class LimitConsumerMemoryExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithRequiredArguments()
    {
        new LimitConsumerMemoryExtension(12345);
    }

    public function testShouldThrowExceptionIfMemoryLimitIsNotInt()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Expected memory limit is int but got: "double"');

        new LimitConsumerMemoryExtension(0.0);
    }

    public function testOnIdleShouldInterruptExecutionIfMemoryLimitReached()
    {
        $context = $this->createFMSContext();
        $context->getLogger()
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('[LimitConsumerMemoryExtension] Interrupt execution as memory limit reached.'))
        ;

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        // test
        $extension = new LimitConsumerMemoryExtension(1);
        $extension->onIdle($context);

        $this->assertTrue($context->isExecutionInterrupted());
    }

    public function testOnPostReceivedShouldInterruptExecutionIfMemoryLimitReached()
    {
        $context = $this->createFMSContext();
        $context->getLogger()
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('[LimitConsumerMemoryExtension] Interrupt execution as memory limit reached.'))
        ;

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        // test
        $extension = new LimitConsumerMemoryExtension(1);
        $extension->onPostReceived($context);

        $this->assertTrue($context->isExecutionInterrupted());
    }

    public function testOnBeforeReceivedShouldInterruptExecutionIfMemoryLimitReached()
    {
        $context = $this->createFMSContext();
        $context->getLogger()
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('[LimitConsumerMemoryExtension] Interrupt execution as memory limit reached.'))
        ;

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        // test
        $extension = new LimitConsumerMemoryExtension(1);
        $extension->onBeforeReceive($context);

        $this->assertTrue($context->isExecutionInterrupted());
    }

    public function testOnBeforeReceiveShouldNotInterruptExecutionIfMemoryLimitIsNotReached()
    {
        $context = $this->createFMSContext();

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        // test
        $extension = new LimitConsumerMemoryExtension(PHP_INT_MAX);
        $extension->onBeforeReceive($context);

        $this->assertFalse($context->isExecutionInterrupted());
    }

    public function testOnIdleShouldNotInterruptExecutionIfMemoryLimitIsNotReached()
    {
        $context = $this->createFMSContext();

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        // test
        $extension = new LimitConsumerMemoryExtension(PHP_INT_MAX);
        $extension->onIdle($context);

        $this->assertFalse($context->isExecutionInterrupted());
    }

    public function testOnPostReceivedShouldNotInterruptExecutionIfMemoryLimitIsNotReached()
    {
        $context = $this->createFMSContext();

        // guard
        $this->assertFalse($context->isExecutionInterrupted());

        // test
        $extension = new LimitConsumerMemoryExtension(PHP_INT_MAX);
        $extension->onPostReceived($context);

        $this->assertFalse($context->isExecutionInterrupted());
    }

    /**
     * @return Context
     */
    protected function createFMSContext()
    {
        $context = new Context($this->createMock(FMSContext::class));
        $context->setLogger($this->createMock(LoggerInterface::class));
        $context->setFMSConsumer($this->createMock(Consumer::class));
        $context->setMessageProcessor($this->createMock(MessageProcessorInterface::class));

        return $context;
    }
}
