<?php
namespace Formapro\MessageQueue\Tests\Rpc;

use Formapro\Jms\JMSConsumer;
use Formapro\MessageQueue\Rpc\Promise;
use Formapro\MessageQueue\Transport\Null\NullMessage;

class PromiseTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithExpectedSetOfArguments()
    {
        new Promise($this->createJMSConsumerMock(), 'aCorrelationId', 2);
    }

    public function testShouldTimeoutIfNoResponseMessage()
    {
        $jmsConsumerMock = $this->createJMSConsumerMock();
        $jmsConsumerMock
            ->expects($this->atLeastOnce())
            ->method('receive')
            ->willReturn(null)
        ;

        $promise = new Promise($jmsConsumerMock, 'aCorrelationId', 2);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Time outed without receiving reply message. Timeout: 2, CorrelationId: aCorrelationId');
        $promise->getMessage();
    }

    public function testShouldReturnReplyMessageIfCorrelationIdSame()
    {
        $correlationId = 'theCorrelationId';

        $replyMessage = new NullMessage();
        $replyMessage->setCorrelationId($correlationId);

        $jmsConsumerMock = $this->createJMSConsumerMock();
        $jmsConsumerMock
            ->expects($this->once())
            ->method('receive')
            ->willReturn($replyMessage)
        ;
        $jmsConsumerMock
            ->expects($this->once())
            ->method('acknowledge')
            ->with($this->identicalTo($replyMessage))
        ;

        $promise = new Promise($jmsConsumerMock, $correlationId, 2);

        $actualReplyMessage = $promise->getMessage();
        $this->assertSame($replyMessage, $actualReplyMessage);
    }

    public function testShouldReQueueIfCorrelationIdNotSame()
    {
        $correlationId = 'theCorrelationId';

        $anotherReplyMessage = new NullMessage();
        $anotherReplyMessage->setCorrelationId('theOtherCorrelationId');

        $replyMessage = new NullMessage();
        $replyMessage->setCorrelationId($correlationId);

        $jmsConsumerMock = $this->createJMSConsumerMock();
        $jmsConsumerMock
            ->expects($this->at(0))
            ->method('receive')
            ->willReturn($anotherReplyMessage)
        ;
        $jmsConsumerMock
            ->expects($this->at(1))
            ->method('reject')
            ->with($this->identicalTo($anotherReplyMessage), true)
        ;
        $jmsConsumerMock
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn($replyMessage)
        ;
        $jmsConsumerMock
            ->expects($this->at(3))
            ->method('acknowledge')
            ->with($this->identicalTo($replyMessage))
        ;

        $promise = new Promise($jmsConsumerMock, $correlationId, 2);

        $actualReplyMessage = $promise->getMessage();
        $this->assertSame($replyMessage, $actualReplyMessage);
    }

    public function testShouldTrySeveralTimesToReceiveReplyMessage()
    {
        $correlationId = 'theCorrelationId';

        $anotherReplyMessage = new NullMessage();
        $anotherReplyMessage->setCorrelationId('theOtherCorrelationId');

        $replyMessage = new NullMessage();
        $replyMessage->setCorrelationId($correlationId);

        $jmsConsumerMock = $this->createJMSConsumerMock();
        $jmsConsumerMock
            ->expects($this->at(0))
            ->method('receive')
            ->willReturn(null)
        ;
        $jmsConsumerMock
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn(null)
        ;
        $jmsConsumerMock
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn($replyMessage)
        ;
        $jmsConsumerMock
            ->expects($this->at(3))
            ->method('acknowledge')
            ->with($this->identicalTo($replyMessage))
        ;

        $promise = new Promise($jmsConsumerMock, $correlationId, 2);

        $actualReplyMessage = $promise->getMessage();
        $this->assertSame($replyMessage, $actualReplyMessage);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSConsumer
     */
    private function createJMSConsumerMock()
    {
        return $this->createMock(JMSConsumer::class);
    }
}