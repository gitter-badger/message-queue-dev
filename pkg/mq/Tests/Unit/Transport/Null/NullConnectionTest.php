<?php
namespace Formapro\MessageQueue\Tests\Unit\Transport\Null;

use Formapro\MessageQueue\Transport\ConnectionInterface;
use Formapro\MessageQueue\Transport\Null\NullConnection;
use Formapro\MessageQueue\Transport\Null\NullSession;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

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
