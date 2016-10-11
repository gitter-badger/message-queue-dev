<?php
namespace FormaPro\MessageQueue\Consumption;

use FormaPro\MessageQueue\Transport\MessageInterface;
use FormaPro\MessageQueue\Transport\SessionInterface;
use Psr\Log\LogLevel;

class Result
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var MessageInterface
     */
    private $message;

    public function __construct($status, $reason = '', $debugLevel = null)
    {

    }

    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;
    }

    public static function createAck($context)
    {
        return new static(MessageProcessorInterface::ACK, $context);
    }

    public static function createReject($context = [])
    {
        return new static(MessageProcessorInterface::REJECT, $context);
    }
}

class MessageProcessor implements MessageProcessorInterface
{
    public function process(MessageInterface $message, SessionInterface $session)
    {
        return Result::createReject([
            'monolog' => [
                'message' => 'shit happened',
                'level' => LogLevel::DEBUG,
                'context' => null, // extension converts somehow message if null
            ]
        ]);
    }
}

class QueueConsumer
{
    function doConsume()
    {
        // .......

        $status = $messageProcessor->process($message, $session);

        if ($status instanceof Result) {
            $context->setStatus($status->getStatus());
            $context->setProcessorContext($status->getProcessorContext());
        } elseif (is_string($status) && in_array($status, ['statuses'])) {
            $context->setStatus($status);
        }

        $extension->onPostProcessor($context);

        // .......
    }
}
