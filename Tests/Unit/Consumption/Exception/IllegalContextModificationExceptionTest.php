<?php
namespace FormaPro\MessageQueue\Tests\Unit\Consumption;

use FormaPro\MessageQueue\Consumption\Exception\ExceptionInterface;
use FormaPro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class IllegalContextModificationExceptionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;
    
    public function testShouldImplementExceptionInterface()
    {
        $this->assertClassImplements(ExceptionInterface::class, IllegalContextModificationException::class);
    }

    public function testShouldExtendLogicException()
    {
        $this->assertClassExtends(\LogicException::class, IllegalContextModificationException::class);
    }
    
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new IllegalContextModificationException();
    }
}
