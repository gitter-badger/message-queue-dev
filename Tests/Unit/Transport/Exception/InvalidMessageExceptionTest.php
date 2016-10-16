<?php
namespace Formapro\MessageQueue\Tests\Unit\Transport\Exception;

use Formapro\MessageQueue\Transport\Exception\Exception as ExceptionInterface;
use Formapro\MessageQueue\Transport\Exception\InvalidMessageException;
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
