<?php
namespace FormaPro\MessageQueue\Tests\Unit\Transport\Exception;

use FormaPro\MessageQueue\Transport\Exception\Exception as ExceptionInterface;
use FormaPro\MessageQueue\Transport\Exception\InvalidMessageException;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

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
