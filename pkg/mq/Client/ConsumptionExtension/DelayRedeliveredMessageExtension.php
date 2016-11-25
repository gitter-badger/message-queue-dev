<?php
namespace Formapro\MessageQueue\Client\ConsumptionExtension;

use Formapro\MessageQueue\Client\DriverInterface;
use Formapro\MessageQueue\Client\Message;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\Result;

class DelayRedeliveredMessageExtension implements ExtensionInterface
{
    use EmptyExtensionTrait;

    const PROPERTY_REDELIVER_COUNT = 'fp-redeliver-count';

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * The number of seconds the message should be delayed.
     *
     * @var int
     */
    private $delay;

    /**
     * @param DriverInterface $driver
     * @param int             $delay  The number of seconds the message should be delayed
     */
    public function __construct(DriverInterface $driver, $delay)
    {
        $this->driver = $driver;
        $this->delay = $delay;
    }

    /**
     * {@inheritdoc}
     */
    public function onPreReceived(Context $context)
    {
        $message = $context->getFMSMessage();
        if (false == $message->isRedelivered()) {
            return;
        }

        $properties = $message->getProperties();
        if (!isset($properties[self::PROPERTY_REDELIVER_COUNT])) {
            $properties[self::PROPERTY_REDELIVER_COUNT] = 1;
        } else {
            ++$properties[self::PROPERTY_REDELIVER_COUNT];
        }

        $delayedMessage = new Message();
        $delayedMessage->setBody($message->getBody());
        $delayedMessage->setHeaders($message->getHeaders());
        $delayedMessage->setProperties($properties);
        $delayedMessage->setDelay($this->delay);

        $this->driver->send($context->getFMSQueue(), $delayedMessage);
        $context->getLogger()->debug('[DelayRedeliveredMessageExtension] Send delayed message');

        $context->setResult(Result::REJECT);
        $context->getLogger()->debug(
            '[DelayRedeliveredMessageExtension] '.
            'Reject redelivered original message by setting reject status to context.'
        );
    }
}
