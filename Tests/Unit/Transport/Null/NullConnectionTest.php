<?php
namespace FormaPro\MessageQueue\Tests\Unit\Transport\Null;

use FormaPro\MessageQueue\Transport\ConnectionInterface;
use FormaPro\MessageQueue\Transport\Null\NullConnection;
use FormaPro\MessageQueue\Transport\Null\NullSession;
use FormaPro\MessageQueue\Test\ClassExtensionTrait;

class NullConnectionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementConnectionInterface()
    {
        $this->assertClassImplements(ConnectionInterface::class, NullConnection::class);
    }
    
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new NullConnection();
    }
    
    public function testShouldCreateNullSession()
    {
        $connection = new NullConnection();

        $this->assertInstanceOf(NullSession::class, $connection->createSession());
    }
}
