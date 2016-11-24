<?php
namespace Formapro\Fms\Tests\Exception;

use Formapro\Fms\Destination;
use Formapro\Fms\Exception\Exception as ExceptionInterface;
use Formapro\Fms\Exception\InvalidDestinationException;
use Formapro\Fms\Test\ClassExtensionTrait;

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
        $this->expectException(InvalidDestinationException::class);
        $this->expectExceptionMessage(
            'The destination must be an instance of Formapro\Fms\Tests\Exception\DestinationBar'.
            ' but it is Formapro\Fms\Tests\Exception\DestinationFoo.'
        );

        InvalidDestinationException::assertDestinationInstanceOf(new DestinationFoo(), DestinationBar::class);
    }

    public function testShouldDoNothingIfAssertDestinationInstanceOfSameAsExpected()
    {
        InvalidDestinationException::assertDestinationInstanceOf(new DestinationFoo(), DestinationFoo::class);
    }
}

class DestinationBar implements Destination
{
}

class DestinationFoo implements Destination
{
}
