<?php
namespace Formapro\AmqpExt;

use Formapro\Fms\Destination;
use Formapro\Fms\InvalidDestinationException;
use Formapro\Fms\InvalidMessageException;
use Formapro\Fms\Message;
use Formapro\Fms\Producer;
use Formapro\Fms\Topic;

class AmqpProducer implements Producer
{
    /**
     * @var \AMQPChannel
     */
    private $amqpChannel;

    /**
     * @param \AMQPChannel $ampqChannel
     */
    public function __construct(\AMQPChannel $ampqChannel)
    {
        $this->amqpChannel = $ampqChannel;
    }

    /**
     * {@inheritdoc}
     *
     * @param AmqpTopic|AmqpQueue $destination
     * @param AmqpMessage         $message
     */
    public function send(Destination $destination, Message $message)
    {
        $destination instanceof Topic
            ? InvalidDestinationException::assertDestinationInstanceOf($destination, AmqpTopic::class)
            : InvalidDestinationException::assertDestinationInstanceOf($destination, AmqpQueue::class)
        ;

        InvalidMessageException::assertMessageInstanceOf($message, AmqpMessage::class);

        $amqpAttributes = $message->getHeaders();

        if ($message->getProperties()) {
            $amqpAttributes['headers'] = $message->getProperties();
        }

        if ($destination instanceof AmqpTopic) {
            $amqpExchange = new \AMQPExchange($this->amqpChannel);
            $amqpExchange->setType($destination->getType());
            $amqpExchange->setName($destination->getTopicName());

            $amqpExchange->publish(
                $message->getBody(),
                $destination->getRoutingKey(),
                $message->getFlags(),
                $amqpAttributes
            );
        } else {
            $amqpExchange = new \AMQPExchange($this->amqpChannel);
            $amqpExchange->setType(AMQP_EX_TYPE_DIRECT);
            $amqpExchange->setName('');

            $amqpExchange->publish(
                $message->getBody(),
                $destination->getQueueName(),
                $message->getFlags(),
                $amqpAttributes
            );
        }
    }
}
