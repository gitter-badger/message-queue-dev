<?php
namespace Formapro\Jms\Tests\Exception;

use Formapro\Jms\Destination;
use Formapro\Jms\Exception\Exception as ExceptionInterface;
use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\Test\ClassExtensionTrait;

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
            'The destination must be an instance of Formapro\Jms\Tests\Exception\DestinationBar'.
            ' but it is Formapro\Jms\Tests\Exception\DestinationFoo.'
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
