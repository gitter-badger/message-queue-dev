<?php
namespace Formapro\Jms\Tests\Exception;

use Formapro\Jms\Exception\Exception as ExceptionInterface;
use Formapro\Jms\Exception\InvalidMessageException;
use Formapro\Jms\Test\ClassExtensionTrait;

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
