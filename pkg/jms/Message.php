<?php
namespace Formapro\Jms;

/**
 * The Message interface is the root interface of all transport messages.
 * Most message-oriented middleware (MOM) products
 * treat messages as lightweight entities that consist of a header and a payload.
 * The header contains fields used for message routing and identification;
 * the payload contains the application data being sent.
 *
 * Within this general form, the definition of a message varies significantly across products.
 *
 * @link https://docs.oracle.com/javaee/7/api/javax/jms/Message.html
 */
interface Message
{
    /**
     * @param string $body
     *
     * @return void
     */
    public function setBody($body);

    /**
     * @return mixed
     */
    public function getBody();

    /**
     * @return void
     */
    public function clearBody();

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function setProperty($name, $value);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getProperty($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function propertyExists($name);

    /**
     * @return void
     */
    public function clearProperties();

    /**
     * @param string $correlationId
     *
     * @return void
     */
    public function setJMSCorrelationID($correlationId);

    /**
     * @return string
     */
    public function getJMSCorrelationID();

    /**
     * @param string $mode
     *
     * @return void
     */
    public function setJMSDeliveryMode($mode);

    /**
     * @return string
     */
    public function getJMSDeliveryMode();

    /**
     * @param int $deliveryTime
     *
     * @return void
     */
    public function setJMSDeliveryTime($deliveryTime);

    /**
     * @return int
     */
    public function getJMSDeliveryTime();

    /**
     * @param Destination $destination
     *
     * @return void
     */
    public function setJMSDestination(Destination $destination);

    /**
     * @return Destination
     */
    public function getJMSDestination();

    /**
     * @param int $expiration
     *
     * @return void
     */
    public function setJMSExpiration($expiration);

    /**
     * @return int
     */
    public function getJMSExpiration();

    /**
     * @param string $messageId
     *
     * @return void
     */
    public function setJMSMessageID($messageId);

    /**
     * @return string
     */
    public function getJMSMessageID();

    /**
     * @param int $priority
     *
     * @return void
     */
    public function setJMSPriority($priority);

    /**
     * @return int
     */
    public function getJMSPriority();

    /**
     * @param bool $redelivered
     *
     * @return void
     */
    public function setJMSRedelivered($redelivered);

    /**
     * @return bool
     */
    public function getJMSRedelivered();

    /**
     * @param Destination $destination
     *
     * @return void
     */
    public function setJMSReplyTo(Destination $destination);

    /**
     * @return Destination
     */
    public function getJMSReplyTo();

    /**
     * @param int $timestamp
     *
     * @return void
     */
    public function setJMSTimestamp($timestamp);

    /**
     * @return int
     */
    public function getJMSTimestamp();
}
