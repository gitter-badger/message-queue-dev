<?php
namespace Formapro\MessageQueueStompTransport\Transport;

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

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->type = self::TYPE_QUEUE;
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
        $name = '/' . $this->getType() . '/' . $this->getQueueName();

        if ($this->getRoutingKey()) {
            $name .= '/'.$this->getRoutingKey();
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getTopicName()
    {
        return $this->name;
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
