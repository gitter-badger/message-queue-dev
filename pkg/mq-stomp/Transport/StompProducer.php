<?php
namespace Formapro\MessageQueueStompTransport\Transport;

use Formapro\Jms\DeliveryMode;
use Formapro\Jms\Destination;
use Formapro\Jms\JMSProducer;
use Formapro\MessageQueue\Transport\Exception\InvalidDeliveryModeException;
use Formapro\MessageQueue\Transport\Exception\InvalidDestinationException;
use Formapro\MessageQueue\Transport\Exception\InvalidMessageException;
use Formapro\MessageQueue\Util\UUID;
use Stomp\Client;
use Stomp\Transport\Message;

class StompProducer implements JMSProducer
{
    /**
     * @var Client
     */
    private $stomp;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var string
     */
    private $deliveryMode;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @param Client $stomp
     */
    public function __construct(Client $stomp)
    {
        $this->stomp = $stomp;
        $this->properties = [];
        $this->deliveryMode = DeliveryMode::PERSISTENT;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($name)
    {
        return $this->propertyExists($name) ? $this->properties[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function clearProperties()
    {
        $this->properties = [];
    }

    /**
     * {@inheritdoc}
     */
    public function propertyExists($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @param string $deliveryMode
     *
     * @return $this
     */
    public function setDeliveryMode($deliveryMode)
    {
        InvalidDeliveryModeException::assertValidDeliveryMode($deliveryMode);

        $this->deliveryMode = $deliveryMode;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryMode()
    {
        return $this->deliveryMode;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $timeToLive
     *
     * @return $this
     */
    public function setTimeToLive($timeToLive)
    {
        $this->ttl = $timeToLive;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeToLive()
    {
        return $this->ttl;
    }

    /**
     * {@inheritdoc}
     *
     * @param StompDestination $destination
     * @param StompMessage $message
     */
    public function send(Destination $destination, $message)
    {
        if (is_string($message)) {
            $message = new StompMessage($message);
        }

        InvalidDestinationException::assertDestinationInstanceOf($destination, StompDestination::class);
        InvalidMessageException::assertMessageInstanceOf($message, StompMessage::class);

        $currentTime = time() * 1000;
        $expiration = $this->ttl ? $currentTime + $this->ttl : 0;

        $message->setJMSMessageID(UUID::generate());
        $message->setJMSDeliveryMode($this->deliveryMode);
        $message->setJMSTimestamp($currentTime);
        $message->setJMSExpiration($expiration);
        $message->setJMSDestination($destination);

        $headers = $this->properties;
        $headers = array_merge($headers, $destination->getHeaders());

        if ($this->deliveryMode === DeliveryMode::PERSISTENT) {
            $headers['persistent'] = true;
        }

        if (null !== $this->priority) {
            $headers['priority'] = $this->priority;
        }

        if (null !== $this->ttl) {
            $headers['expiration'] = $this->ttl;
        }

        $headers = array_merge($headers, $message->toStompHeaders());
        $headers = StompHeadersEncoder::encode($headers);

        $stompMessage = new Message($message->getBody(), $headers);

        $this->stomp->send($destination->getStompName(), $stompMessage);
    }
}
