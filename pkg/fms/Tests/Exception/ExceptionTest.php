<?php
namespace Formapro\Fms\Tests\Exception;

use Formapro\Fms\Exception\Exception;
use Formapro\Fms\Exception\ExceptionInterface;
use Formapro\Fms\Test\ClassExtensionTrait;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldBeSubClassOfException()
    {
        $this->assertClassExtends(\Exception::class, Exception::class);
    }

    public function testShouldImplementExceptionInterface()
    {
        $this->assertClassImplements(ExceptionInterface::class, Exception::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new Exception();
    }
}
