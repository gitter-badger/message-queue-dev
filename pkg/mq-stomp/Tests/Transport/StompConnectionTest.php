<?php
namespace Formapro\MessageQueueStompTransport\Tests\Transport;

use Formapro\MessageQueue\Transport\ConnectionInterface;
use Formapro\MessageQueueStompTransport\Test\ClassExtensionTrait;
use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompConnection;
use Formapro\MessageQueueStompTransport\Transport\StompSession;

class StompConnectionTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementsConnectionInterface()
    {
        $this->assertClassImplements(ConnectionInterface::class, StompConnection::class);
    }

    public function testCouldBeConstructedWithRequiredArguments()
    {
        new StompConnection($this->createStompClientMock());
    }

    public function testShouldCreateSessionInstance()
    {
        $connection = new StompConnection($this->createStompClientMock());

        $this->assertInstanceOf(StompSession::class, $connection->createSession());
    }

    public function testShouldCloseConnections()
    {
        $client = $this->createStompClientMock();
        $client
            ->expects($this->once())
            ->method('disconnect')
        ;

        $connection = new StompConnection($client);

        $connection->close();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|BufferedStompClient
     */
    private function createStompClientMock()
    {
        return $this->createMock(BufferedStompClient::class);
    }
}
