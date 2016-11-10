<?php
namespace Formapro\MessageQueue\Tests\Unit\Transport\Exception;

use Formapro\Jms\Exception\Exception as ExceptionInterface;
use Formapro\Jms\Exception\InvalidMessageException;
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
