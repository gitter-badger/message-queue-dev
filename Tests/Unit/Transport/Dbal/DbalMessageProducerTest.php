<?php
namespace FormaPro\MessageQueue\Tests\Unit\Transport\Dbal;

use Doctrine\DBAL\Connection;
use FormaPro\MessageQueue\Transport\Dbal\DbalConnection;
use FormaPro\MessageQueue\Transport\Dbal\DbalDestination;
use FormaPro\MessageQueue\Transport\Dbal\DbalMessage;
use FormaPro\MessageQueue\Transport\Dbal\DbalMessageProducer;
use FormaPro\MessageQueue\Transport\Exception\Exception;
use FormaPro\MessageQueue\Transport\Exception\InvalidDestinationException;
use FormaPro\MessageQueue\Transport\Exception\InvalidMessageException;
use FormaPro\MessageQueue\Transport\Null\NullQueue;

class DbalMessageProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithRequiredArguments()
    {
        new DbalMessageProducer($this->createConnectionMock());
    }

    public function testShouldThrowIfBodyOfInvalidType()
    {
        $this->setExpectedException(
            InvalidMessageException::class,
            'The message body must be a scalar or null. Got: stdClass'
        );

        $producer = new DbalMessageProducer($this->createConnectionMock());

        $message = new DbalMessage();
        $message->setBody(new \stdClass());

        $producer->send(new DbalDestination(''), $message);
    }

    public function testShouldThrowIfDestinationOfInvalidType()
    {
        $this->setExpectedException(
            InvalidDestinationException::class,
            'The destination must be an instance of '.
            'FormaPro\MessageQueue\Transport\Dbal\DbalDestination but it is '.
            'FormaPro\MessageQueue\Transport\Null\NullQueue.'
        );

        $producer = new DbalMessageProducer($this->createConnectionMock());

        $producer->send(new NullQueue(''), new DbalMessage());
    }

    public function testShouldThrowIfInsertMessageFailed()
    {
        $dbal = $this->createDBALConnectionMock();
        $dbal
            ->expects($this->once())
            ->method('insert')
            ->will($this->throwException(new \Exception('error message')))
        ;

        $connection = $this->createConnectionMock();
        $connection
            ->expects($this->once())
            ->method('getDBALConnection')
            ->will($this->returnValue($dbal))
        ;

        $destination = new DbalDestination('queue-name');
        $message = new DbalMessage();

        $this->setExpectedException(
            Exception::class,
            'The transport fails to send the message due to some internal error.'
        );

        $producer = new DbalMessageProducer($connection);
        $producer->send($destination, $message);
    }

    public function testShouldSendMessage()
    {
        $expectedMessage = [
            'body' => 'body',
            'headers' => '{"hkey":"hvalue"}',
            'properties' => '{"pkey":"pvalue"}',
            'priority' => 123,
            'queue' => 'queue-name',
        ];

        $dbal = $this->createDBALConnectionMock();
        $dbal
            ->expects($this->once())
            ->method('insert')
            ->with('tableName', $expectedMessage)
        ;

        $connection = $this->createConnectionMock();
        $connection
            ->expects($this->once())
            ->method('getDBALConnection')
            ->will($this->returnValue($dbal))
        ;
        $connection
            ->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('tableName'))
        ;

        $destination = new DbalDestination('queue-name');
        $message = new DbalMessage();
        $message->setBody('body');
        $message->setHeaders(['hkey' => 'hvalue']);
        $message->setProperties(['pkey' => 'pvalue']);
        $message->setPriority(123);

        $producer = new DbalMessageProducer($connection);
        $producer->send($destination, $message);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DbalConnection
     */
    private function createConnectionMock()
    {
        return $this->createMock(DbalConnection::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private function createDBALConnectionMock()
    {
        return $this->createMock(Connection::class);
    }
}
