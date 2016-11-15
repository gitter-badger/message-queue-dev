<?php
namespace Formapro\MessageQueue\Tests\Consumption;

use Formapro\MessageQueue\Consumption\Exception\ConsumptionInterruptedException;
use Formapro\MessageQueue\Consumption\Exception\ExceptionInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

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
