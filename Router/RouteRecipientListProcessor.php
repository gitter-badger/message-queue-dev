<?php
namespace Formapro\MessageQueue\Router;

use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Transport\MessageInterface;
use Formapro\MessageQueue\Transport\SessionInterface as TransportSession;

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
    public function process(MessageInterface $message, TransportSession $session)
    {
        $producer = $session->createProducer();
        foreach ($this->router->route($message) as $recipient) {
            $producer->send($recipient->getDestination(), $recipient->getMessage());
        }

        return Result::ACK;
    }
}
