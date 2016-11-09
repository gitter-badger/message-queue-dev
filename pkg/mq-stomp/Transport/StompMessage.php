<?php
namespace Formapro\MessageQueueStompTransport\Transport;

use Formapro\Jms\Destination;
use Formapro\Jms\Message;
use Stomp\Transport\Frame;

class StompMessage implements Message
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $jmsProperties;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var Frame
     */
    private $frame;

    /**
     * @param string $body
     * @param array $properties
     */
    public function __construct($body = '', array $properties = [])
    {
        $this->body = $body;
        $this->jmsProperties = [];
        $this->properties = $properties;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function clearBody()
    {
        $this->body = '';
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($name)
    {
        return  $this->propertyExists($name) ? $this->properties[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function propertyExists($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function clearProperties()
    {
        $this->properties = [];
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    private function setJmsProperty($name, $value)
    {
        $this->jmsProperties[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getJmsProperty($name)
    {
        return array_key_exists($name, $this->jmsProperties) ? $this->jmsProperties[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSCorrelationID($correlationId)
    {
        $this->setJmsProperty('JMSCorrelationID', (string) $correlationId);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSCorrelationID()
    {
        return $this->getJmsProperty('JMSCorrelationID');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSDeliveryMode($mode)
    {
        $this->setJmsProperty('JMSDeliveryMode', $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSDeliveryMode()
    {
        return $this->getJmsProperty('JMSDeliveryMode');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSDeliveryTime($deliveryTime)
    {
        $this->setJmsProperty('JMSDeliveryTime', $deliveryTime);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSDeliveryTime()
    {
        return $this->getJmsProperty('JMSDeliveryTime');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSDestination(Destination $destination)
    {
        $this->setJmsProperty('JMSDestination', $destination);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSDestination()
    {
        return $this->getJmsProperty('JMSDestination');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSExpiration($expiration)
    {
        $this->setJmsProperty('JMSExpiration', $expiration);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSExpiration()
    {
        return $this->getJmsProperty('JMSExpiration');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSMessageID($messageId)
    {
        $this->setJmsProperty('JMSMessageID', $messageId);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSMessageID()
    {
        return $this->getJmsProperty('JMSMessageID');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSPriority($priority)
    {
        $this->setJmsProperty('JMSPriority', $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSPriority()
    {
        return $this->getJmsProperty('JMSPriority');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSRedelivered($redelivered)
    {
        $this->setJmsProperty('JMSRedelivered', $redelivered);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSRedelivered()
    {
        return $this->getJmsProperty('JMSRedelivered');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSReplyTo(Destination $destination)
    {
        $this->setJmsProperty('JMSReplyTo', $destination);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSReplyTo()
    {
        return $this->getJmsProperty('JMSReplyTo');
    }

    /**
     * {@inheritdoc}
     */
    public function setJMSTimestamp($timestamp)
    {
        $this->setJmsProperty('JMSTimestamp', $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function getJMSTimestamp()
    {
        return $this->getJmsProperty('JMSTimestamp');
    }

    /**
     * @return Frame
     */
    public function getFrame()
    {
        return $this->frame;
    }

    /**
     * @param Frame $frame
     */
    public function setFrame(Frame $frame)
    {
        $this->frame = $frame;
    }
}
