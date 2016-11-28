<?php
namespace Formapro\MessageQueue\Tests\Consumption;

use Formapro\MessageQueue\Consumption\CallbackMessageProcessor;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueue\Transport\Null\NullContext;
use Formapro\MessageQueue\Transport\Null\NullMessage;

class CallbackMessageProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, CallbackMessageProcessor::class);
    }

    public function testCouldBeConstructedWithCallableAsArgument()
    {
        new CallbackMessageProcessor(function () {
        });
    }

    public function testShouldCallCallbackAndProxyItsReturnedValue()
    {
        $expectedMessage = new NullMessage();
        $expectedContext = new NullContext();

        $processor = new CallbackMessageProcessor(function ($message, $context) use ($expectedMessage, $expectedContext) {
            $this->assertSame($expectedMessage, $message);
            $this->assertSame($expectedContext, $context);

            return 'theStatus';
        });

        $this->assertSame('theStatus', $processor->process($expectedMessage, $expectedContext));
    }
}
