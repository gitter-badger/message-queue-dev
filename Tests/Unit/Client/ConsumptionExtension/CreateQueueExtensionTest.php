<?php
namespace FormaPro\MessageQueue\Tests\Unit\Client\ConsumptionExtension;

use FormaPro\MessageQueue\Consumption\Context;
use FormaPro\MessageQueue\Consumption\ExtensionInterface;
use FormaPro\MessageQueue\Client\ConsumptionExtension\CreateQueueExtension;
use FormaPro\MessageQueue\Client\DriverInterface;
use FormaPro\MessageQueue\Transport\Null\NullQueue;
use FormaPro\MessageQueue\Transport\SessionInterface;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;
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

        $context = new Context($this->createSessionMock());
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

        $context = new Context($this->createSessionMock());
        $context->setLogger($loggerMock);
        $context->setQueue(new NullQueue('theQueueName1'));

        $extension->onBeforeReceive($context);
        $extension->onBeforeReceive($context);

        $context = new Context($this->createSessionMock());
        $context->setLogger($loggerMock);
        $context->setQueue(new NullQueue('theQueueName2'));

        $extension->onBeforeReceive($context);
        $extension->onBeforeReceive($context);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    protected function createSessionMock()
    {
        return $this->createMock(SessionInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DriverInterface
     */
    protected function createDriverMock()
    {
        return $this->createMock(DriverInterface::class);
    }
}
