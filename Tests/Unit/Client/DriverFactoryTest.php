<?php
namespace Formapro\MessageQueue\Tests\Unit\Client;

use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\DriverFactory;
use Formapro\MessageQueue\Client\NullDriver;
use Formapro\MessageQueue\Transport\ConnectionInterface;
use Formapro\MessageQueue\Transport\Null\NullConnection;
use Formapro\MessageQueue\Transport\Null\NullSession;

class DriverFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldCreateNullSessionInstance()
    {
        $config = new Config('', '', '', '');
        $connection = new NullConnection();

        $factory = new DriverFactory([NullConnection::class => NullDriver::class]);
        $driver = $factory->create($connection, $config);

        self::assertInstanceOf(NullDriver::class, $driver);
        self::assertAttributeInstanceOf(NullSession::class, 'session', $driver);
        self::assertAttributeSame($config, 'config', $driver);
    }

    public function testShouldThrowExceptionIfUnexpectedConnectionInstance()
    {
        $factory = new DriverFactory([]);

        $this->setExpectedException(\LogicException::class, 'Unexpected connection instance: "Mock_Connection');
        $factory->create($this->createMock(ConnectionInterface::class), new Config('', '', '', ''));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NullSession
     */
    protected function createNullSessionMock()
    {
        return $this->createMock(NullSession::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|NullConnection
     */
    protected function createNullConnectionMock()
    {
        return $this->createMock(NullConnection::class);
    }
}
