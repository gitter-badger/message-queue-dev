<?php
namespace FormaPro\MessageQueue\Tests\Unit\Consumption;

use FormaPro\MessageQueue\Consumption\Exception\ConsumptionInterruptedException;
use FormaPro\MessageQueue\Consumption\Exception\ExceptionInterface;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class ConsumptionInterruptedExceptionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;
    
    public function testShouldImplementExceptionInterface()
    {
        $this->assertClassImplements(ExceptionInterface::class, ConsumptionInterruptedException::class);
    }

    public function testShouldExtendLogicException()
    {
        $this->assertClassExtends(\LogicException::class, ConsumptionInterruptedException::class);
    }
    
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new ConsumptionInterruptedException();
    }
}
