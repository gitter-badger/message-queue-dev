<?php
namespace Formapro\MessageQueue\Tests\Unit\Transport\Exception;

use Formapro\MessageQueue\Tests\Unit\Mock\DestinationBar;
use Formapro\MessageQueue\Tests\Unit\Mock\DestinationFoo;
use Formapro\Jms\Exception\Exception as ExceptionInterface;
use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

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
            'The destination must be an instance of Formapro\MessageQueue\Tests\Unit\Mock\DestinationBar'.
            ' but it is Formapro\MessageQueue\Tests\Unit\Mock\DestinationFoo.'
        );

        InvalidDestinationException::assertDestinationInstanceOf(new DestinationFoo(), DestinationBar::class);
    }

    public function testShouldDoNothingIfAssertDestinationInstanceOfSameAsExpected()
    {
        InvalidDestinationException::assertDestinationInstanceOf(new DestinationFoo(), DestinationFoo::class);
    }
}
