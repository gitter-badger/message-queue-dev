<?php
namespace Formapro\Fms\Tests\Exception;

use Formapro\Fms\Exception as ExceptionInterface;
use Formapro\Fms\InvalidMessageException;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

class InvalidMessageExceptionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldBeSubClassOfException()
    {
        $this->assertClassExtends(ExceptionInterface::class, InvalidMessageException::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new InvalidMessageException();
    }
}
