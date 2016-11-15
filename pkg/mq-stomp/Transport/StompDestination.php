<?php
namespace Formapro\Stomp\Transport;

use Formapro\Jms\Queue;
use Formapro\Jms\Topic;

class StompDestination implements Topic, Queue
{
    const TYPE_TOPIC = 'topic';
    const TYPE_EXCHANGE = 'exchange';
    const TYPE_QUEUE = 'queue';
    const TYPE_AMQ_QUEUE = 'amq/queue';
    const TYPE_TEMP_QUEUE = 'temp-queue';

    const HEADER_DURABLE = 'durable';
    const HEADER_AUTO_DELETE = 'auto-delete';
    const HEADER_EXCLUSIVE = 'exclusive';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var array
     */
    private $headers;

    public function __construct()
    {
        $this->headers = [
            self::HEADER_DURABLE => false,
            self::HEADER_AUTO_DELETE => true,
            self::HEADER_EXCLUSIVE => false,
        ];
    }

    /**
     * @return string
     */
    public function getStompName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setStompName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        if (empty($this->getType()) || empty($this->getStompName())) {
            throw new \LogicException('Destination type or name is not set');
        }

        $name = '/' . $this->getType() . '/' . $this->getStompName();

        if ($this->getRoutingKey()) {
            $name .= '/'.$this->getRoutingKey();
        }

        return $name;
    }

    /**
     * @param string $destination
     */
    public function setQueueName($destination)
    {
        if (0 === strpos($destination, '/')) {
            $this->setStompName($destination);
            $this->setType(self::TYPE_QUEUE);

            return;
        }

        $types = [
            self::TYPE_TOPIC,
            self::TYPE_EXCHANGE,
            self::TYPE_QUEUE,
            self::TYPE_AMQ_QUEUE,
            self::TYPE_TEMP_QUEUE,
        ];

        $dest = $destination;
        $type = null;
        $name = null;
        $routingKey = null;

        foreach ($types as $_type) {
            $typePrefix = '/'.$_type.'/';
            if (0 === strpos($dest, $typePrefix)) {
                $type = $_type;
                $dest = substr($dest, strlen($typePrefix));

                break;
            }
        }

        if (null === $type) {
            throw new \LogicException(sprintf('Destination name is invalid, cant find type: "%s"', $destination));
        }

        $pieces = explode('/', $dest);

        if (count($pieces) > 2) {
            throw new \LogicException(sprintf('Destination name is invalid, found extra / char: "%s"', $destination));
        }

        if (empty($pieces[0])) {
            throw new \LogicException(sprintf('Destination name is invalid, name is empty: "%s"', $destination));
        }

        $name = $pieces[0];

        if (isset($pieces[1])) {
            if (empty($pieces[1])) {
                throw new \LogicException(sprintf('Destination name is invalid, routing key is empty: "%s"', $destination));
            }

            $routingKey = $pieces[1];
        }

        $this->setType($type);
        $this->setStompName($name);
        $this->setRoutingKey($routingKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getTopicName()
    {
        return $this->getQueueName();
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $types = [
            self::TYPE_TOPIC,
            self::TYPE_EXCHANGE,
            self::TYPE_QUEUE,
            self::TYPE_AMQ_QUEUE,
            self::TYPE_TEMP_QUEUE,
        ];

        if (false == in_array($type, $types)) {
            throw new \LogicException(sprintf('Invalid destination type: "%s"', $type));
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * @param string $routingKey
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
    }

    /**
     * @return bool
     */
    public function isDurable()
    {
        return $this->getHeader(self::HEADER_DURABLE, false);
    }

    /**
     * @param bool $durable
     */
    public function setDurable($durable)
    {
        $this->setHeader(self::HEADER_DURABLE, (bool) $durable);
    }

    /**
     * @return bool
     */
    public function isAutoDelete()
    {
        return $this->getHeader(self::HEADER_AUTO_DELETE, false);
    }

    /**
     * @param bool $autoDelete
     */
    public function setAutoDelete($autoDelete)
    {
        $this->setHeader(self::HEADER_AUTO_DELETE, (bool) $autoDelete);
    }

    /**
     * @return bool
     */
    public function isExclusive()
    {
        return $this->getHeader(self::HEADER_EXCLUSIVE, false);
    }

    /**
     * @param bool $exclusive
     */
    public function setExclusive($exclusive)
    {
        $this->setHeader(self::HEADER_EXCLUSIVE, (bool) $exclusive);
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name, $default = null)
    {
        return array_key_exists($name, $this->headers) ? $this->headers[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }
}
