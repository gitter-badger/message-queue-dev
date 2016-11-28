<?php
namespace Formapro\MessageQueue\Consumption;

use Formapro\Fms\Context as FMSContext;
use Formapro\Fms\Message;

class CallbackMessageProcessor implements MessageProcessorInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, FMSContext $context)
    {
        return call_user_func($this->callback, $message, $context);
    }
}
