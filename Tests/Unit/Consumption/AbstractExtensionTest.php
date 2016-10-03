<?php
namespace FormaPro\MessageQueue\Tests\Unit\Consumption;

use FormaPro\MessageQueue\Consumption\AbstractExtension;
use FormaPro\MessageQueue\Consumption\ExtensionInterface;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class AbstractExtensionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementExtensionInterface()
    {
        $this->assertClassImplements(ExtensionInterface::class, AbstractExtension::class);
    }
}
