<?php
namespace Formapro\MessageQueue\Router;

use Formapro\Jms\JMSContext;
use Formapro\Jms\Message;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\Result;

class RouteRecipientListProcessor implements MessageProcessorInterface
{
    /**
     * @var RecipientListRouterInterface
     */
    private $router;

    /**
     * @param RecipientListRouterInterface $router
     */
    public function __construct(RecipientListRouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, JMSContext $context)
    {
        $producer = $context->createProducer();
        foreach ($this->router->route($message) as $recipient) {
            $producer->send($recipient->getDestination(), $recipient->getMessage());
        }

        return Result::ACK;
    }
}
