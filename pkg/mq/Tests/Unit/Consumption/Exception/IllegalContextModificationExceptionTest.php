<?php
namespace Formapro\MessageQueue\Tests\Unit\Consumption;

use Formapro\MessageQueue\Consumption\Exception\ExceptionInterface;
use Formapro\MessageQueue\Consumption\Exception\IllegalContextModificationException;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

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
