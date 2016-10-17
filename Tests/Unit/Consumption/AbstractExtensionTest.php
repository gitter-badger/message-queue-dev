<?php
namespace Formapro\MessageQueue\Tests\Unit\Consumption;

use Formapro\MessageQueue\Consumption\AbstractExtension;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

class AbstractExtensionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementExtensionInterface()
    {
        $this->assertClassImplements(ExtensionInterface::class, AbstractExtension::class);
    }
}
