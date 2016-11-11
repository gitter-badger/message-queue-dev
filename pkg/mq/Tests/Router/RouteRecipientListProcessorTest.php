<?php
namespace Formapro\MessageQueue\Tests\Router;

use Formapro\Jms\JMSContext;
use Formapro\Jms\JMSProducer;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Router\Recipient;
use Formapro\MessageQueue\Router\RecipientListRouterInterface;
use Formapro\MessageQueue\Router\RouteRecipientListProcessor;
use Formapro\MessageQueue\Transport\Null\NullMessage;
use Formapro\MessageQueue\Transport\Null\NullQueue;
use Formapro\MessageQueue\Test\ClassExtensionTrait;

class RouteRecipientListProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, RouteRecipientListProcessor::class);
    }

    public function testCouldBeConstructedWithRouterAsFirstArgument()
    {
        new RouteRecipientListProcessor($this->createRecipientListRouterMock());
    }

    public function testShouldProduceRecipientsMessagesAndAckOriginalMessage()
    {
        $fooRecipient = new Recipient(new NullQueue('aName'), new NullMessage());
        $barRecipient = new Recipient(new NullQueue('aName'), new NullMessage());

        $originalMessage = new NullMessage();

        $routerMock = $this->createRecipientListRouterMock();
        $routerMock
            ->expects($this->once())
            ->method('route')
            ->with($this->identicalTo($originalMessage))
            ->willReturn([$fooRecipient, $barRecipient])
        ;

        $producerMock = $this->createProducerMock();
        $producerMock
            ->expects($this->at(0))
            ->method('send')
            ->with($this->identicalTo($fooRecipient->getDestination()), $this->identicalTo($fooRecipient->getMessage()))
        ;
        $producerMock
            ->expects($this->at(1))
            ->method('send')
            ->with($this->identicalTo($barRecipient->getDestination()), $this->identicalTo($barRecipient->getMessage()))
        ;

        $sessionMock = $this->createContextMock();
        $sessionMock
            ->expects($this->once())
            ->method('createProducer')
            ->willReturn($producerMock)
        ;

        $processor = new RouteRecipientListProcessor($routerMock);

        $status = $processor->process($originalMessage, $sessionMock);

        $this->assertEquals(Result::ACK, $status);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSProducer
     */
    protected function createProducerMock()
    {
        return $this->createMock(JMSProducer::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|JMSContext
     */
    protected function createContextMock()
    {
        return $this->createMock(JMSContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RecipientListRouterInterface
     */
    protected function createRecipientListRouterMock()
    {
        return $this->createMock(RecipientListRouterInterface::class);
    }
}
