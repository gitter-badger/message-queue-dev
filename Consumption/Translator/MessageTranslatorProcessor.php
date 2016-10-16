<?php
namespace Formapro\MessageQueue\Consumption\Translator;

use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\MessageProducerInterface;
use Formapro\MessageQueue\Transport\SessionInterface;

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

        return self::ACK;
    }
}
