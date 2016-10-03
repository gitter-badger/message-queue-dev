<?php
namespace FormaPro\MessageQueue\Tests\Unit\Transport\Exception;

use FormaPro\MessageQueue\Tests\Unit\Mock\DestinationBar;
use FormaPro\MessageQueue\Tests\Unit\Mock\DestinationFoo;
use FormaPro\MessageQueue\Transport\Exception\Exception as ExceptionInterface;
use FormaPro\MessageQueue\Transport\Exception\InvalidDestinationException;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class InvalidDestinationExceptionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;
    
    public function testShouldBeSubClassOfException()
    {
        $this->assertClassExtends(ExceptionInterface::class, InvalidDestinationException::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new InvalidDestinationException();
    }

    public function testThrowIfAssertDestinationInstanceOfNotSameAsExpected()
    {
        $this->setExpectedException(
            InvalidDestinationException::class,
            'The destination must be an instance of FormaPro\MessageQueue\Tests\Unit\Mock\DestinationBar'.
            ' but it is FormaPro\MessageQueue\Tests\Unit\Mock\DestinationFoo.'
        );

        InvalidDestinationException::assertDestinationInstanceOf(new DestinationFoo(), DestinationBar::class);
    }

    public function testShouldDoNothingIfAssertDestinationInstanceOfSameAsExpected()
    {
        InvalidDestinationException::assertDestinationInstanceOf(new DestinationFoo(), DestinationFoo::class);
    }
}
