<?php
namespace Formapro\Fms\Tests\Exception;

use Formapro\Fms\DeliveryMode;
use Formapro\Fms\ExceptionInterface;
use Formapro\Fms\InvalidDeliveryModeException;
use Formapro\Fms\Test\ClassExtensionTrait;

class InvalidDeliveryModeExceptionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldBeSubClassOfException()
    {
        $this->assertClassExtends(ExceptionInterface::class, InvalidDeliveryModeException::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new InvalidDeliveryModeException();
    }

    public function testThrowIfDeliveryModeIsNotValid()
    {
        $this->expectException(InvalidDeliveryModeException::class);
        $this->expectExceptionMessage('The delivery mode must be one of [2,1].');

        InvalidDeliveryModeException::assertValidDeliveryMode('is-not-valid');
    }

    public function testShouldDoNothingIfDeliveryModeIsValid()
    {
        InvalidDeliveryModeException::assertValidDeliveryMode(DeliveryMode::PERSISTENT);
        InvalidDeliveryModeException::assertValidDeliveryMode(DeliveryMode::NON_PERSISTENT);
    }
}
