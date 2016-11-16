<?php
namespace Formapro\AmqpExt;

use Formapro\Jms\Destination;
use Formapro\Jms\Exception\InvalidDestinationException;
use Formapro\Jms\Exception\InvalidMessageException;
use Formapro\Jms\JMSProducer;
use Formapro\Jms\Message;
use Formapro\Jms\Topic;

class AmqpProducer implements JMSProducer
{
    /**
     * @var \AMQPChannel
     */
    private $channel;

    /**
     * @param \AMQPChannel $channel
     */
    public function __construct(\AMQPChannel $channel)
    {
        $this->channel = $channel;
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
            $amqpAttributes['application_headers'] = $message->getProperties();
        }

//        $amqpMessage = new \AMQPMessage($message->getBody(), $amqpAttributes);

//        if ($destination instanceof AmqpTopic) {
//            $amqpExchange = new \AMQPExchange($this->channel);
//            $amqpExchange->setType($destination);
//            $amqpExchange->setName('');

//            $amqpExchange->publish(
//                $message->getBody(),
//                $destination->getQueueName(),
//                $message->getFlags(),
//                $amqpAttributes
//            );

//            $this->channel->pr

//            $this->channel->basic_publish(
//                $amqpMessage,
//                $destination->getTopicName(),
//                $destination->getRoutingKey(),
//                $message->isMandatory(),
//                $message->isImmediate(),
//                $message->getTicket()
//            );
//        } else {
//            $amqpExchange = new \AMQPExchange($this->channel);
//            $amqpExchange->setType(AMQP_EX_TYPE_DIRECT);
//            $amqpExchange->setName('');

//            $amqpExchange->publish(
//                $message->getBody(),
//                $destination->getQueueName(),
//                $message->getFlags(),
//                $amqpAttributes
//            );
//        }
    }
}
