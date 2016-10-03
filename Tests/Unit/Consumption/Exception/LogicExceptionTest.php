<?php
namespace FormaPro\MessageQueue\Tests\Unit\Consumption;

use FormaPro\MessageQueue\Consumption\Exception\ExceptionInterface;
use FormaPro\MessageQueue\Consumption\Exception\LogicException;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class LogicExceptionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;
    
    public function testShouldImplementExceptionInterface()
    {
        $this->assertClassImplements(ExceptionInterface::class, LogicException::class);
    }

    public function testShouldExtendLogicException()
    {
        $this->assertClassExtends(\LogicException::class, LogicException::class);
    }
    
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new LogicException();
    }
}
