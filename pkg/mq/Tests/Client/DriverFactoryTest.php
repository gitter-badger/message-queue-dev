<?php
namespace Formapro\MessageQueue\Tests\Client;

use Formapro\Jms\JMSContext;
use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\DriverFactory;
use Formapro\MessageQueue\Client\NullDriver;
use Formapro\MessageQueue\Transport\Null\NullContext;

class DriverFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldCreateNullSessionInstance()
    {
        $config = new Config('', '', '', '');
        $context = new NullContext();

        $factory = new DriverFactory([NullContext::class => NullDriver::class]);
        $driver = $factory->create($context, $config);

        self::assertInstanceOf(NullDriver::class, $driver);
        self::assertAttributeInstanceOf(NullContext::class, 'session', $driver);
        self::assertAttributeSame($config, 'config', $driver);
    }

    public function testShouldThrowExceptionIfUnexpectedConnectionInstance()
    {
        $factory = new DriverFactory([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unexpected context instance: "Mock_JMSContext');

        $factory->create($this->createMock(JMSContext::class), new Config('', '', '', ''));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NullContext
     */
    protected function createNullContextMock()
    {
        return $this->createMock(NullContext::class);
    }
}
