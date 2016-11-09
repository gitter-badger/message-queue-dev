<?php
namespace Formapro\Jms;


interface JMSProducer
{
    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setProperty($name, $value);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getProperty($name);

    /**
     * @return $this
     */
    public function clearProperties();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function propertyExists($name);

    /**
     * @param string $deliveryMode
     *
     * @return $this
     */
    public function setDeliveryMode($deliveryMode);

    /**
     * @return string
     */
    public function getDeliveryMode();

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $timeToLive
     *
     * @return $this
     */
    public function setTimeToLive($timeToLive);

    /**
     * @return int
     */
    public function getTimeToLive();

    /**
     * @param Destination $destination
     * @param string|Message $message
     *
     * @return $this
     */
    public function send(Destination $destination, $message);
}