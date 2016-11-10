<?php
namespace Formapro\MessageQueue\Tests\Unit\Transport\Exception;

use Formapro\Jms\Exception\ExceptionInterface;
use Formapro\Jms\Exception\Exception;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

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
