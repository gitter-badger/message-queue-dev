<?php
namespace FormaPro\MessageQueue\Consumption\Translator;

use FormaPro\MessageQueue\Consumption\MessageProcessorInterface;
use FormaPro\MessageQueue\Consumption\MessageStatus;
use FormaPro\MessageQueue\Transport\MessageInterface;
use FormaPro\MessageQueue\Transport\MessageProducerInterface;
use FormaPro\MessageQueue\Transport\SessionInterface;

class MessageTranslatorProcessor implements MessageProcessorInterface
{
    /**
     * @var string
     */
    protected $topicName;

    /**
     * @param string $topicName
     */
    public function __construct($topicName)
    {
        $this->topicName = $topicName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $topic = $session->createTopic($this->topicName);
        $newMessage = $session->createMessage($message->getBody(), $message->getProperties(), $message->getHeaders());

        $session->createProducer()->send($topic, $newMessage);

        return MessageStatus::ACK;
    }
}
