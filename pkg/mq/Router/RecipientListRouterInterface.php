<?php
namespace Formapro\MessageQueue\Router;

use Formapro\Jms\Message;

interface RecipientListRouterInterface
{
    /**
     * @param Message $message
     *
     * @return \Traversable|Recipient[]
     */
    public function route(Message $message);
}
