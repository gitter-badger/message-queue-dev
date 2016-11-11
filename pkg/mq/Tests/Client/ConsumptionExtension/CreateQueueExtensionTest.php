<?php
namespace Formapro\MessageQueue\Tests\Client\ConsumptionExtension;

use Formapro\Jms\JMSContext;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Client\ConsumptionExtension\CreateQueueExtension;
use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Psr\Log\LoggerInterface;

class CreateQueueExtensionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementExtensionInterface()
    {
        $this->assertClassImplements(ExtensionInterface::class, CreateQueueExtension::class);
    }

    public function testCouldBeConstructedWithRequiredArguments()
    {
        new CreateQueueExtension($this->createDriverMock());
    }

    public function testShouldCreateQueueUsingQueueNameFromContext()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('debug')
            ->with('[CreateQueueExtension] Make sure the queue theQueueName exists on a broker side.')
        ;

        $context = new Context($this->createContextMock());
        $context->setQueue(new NullQueue('theQueueName'));
        $context->setLogger($loggerMock);

        $driverMock = $this->createDriverMock();
        $driverMock
            ->expects($this->once())
            ->method('createQueue')
            ->with('theQueueName')
        ;

        $extension = new CreateQueueExtension($driverMock);

        $extension->onBeforeReceive($context);
    }

    public function testShouldCreateSameQueueOnlyOnce()
    {
        $driverMock = $this->createDriverMock();
        $driverMock
            ->expects($this->at(0))
            ->method('createQueue')
            ->with('theQueueName1')
        ;
        $driverMock
            ->expects($this->at(1))
            ->method('createQueue')
            ->with('theQueueName2')
        ;

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->at(0))
            ->method('debug')
            ->with('[CreateQueueExtension] Make sure the queue theQueueName1 exists on a broker side.')
        ;
        $loggerMock
            ->expects($this->at(1))
            ->method('debug')
            ->with('[CreateQueueExtension] Make sure the queue theQueueName2 exists on a broker side.')
        ;

        $extension = new CreateQueueExtension($driverMock);

        $context = new Context($this->createContextMock());
        $context->setLogger($loggerMock);
        $context->setQueue(new NullQueue('theQueueName1'));

        $extension->onBeforeReceive($context);
        $extension->onBeforeReceive($context);

        $context = new Context($this->createContextMock());
        $context->setLogger($loggerMock);
        $context->setQueue(new NullQueue('theQueueName2'));

        $extension->onBeforeReceive($context);
        $extension->onBeforeReceive($context);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSContext
     */
    protected function createContextMock()
    {
        return $this->createMock(JMSContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DriverInterface
     */
    protected function createDriverMock()
    {
        return $this->createMock(DriverInterface::class);
    }
}
