<?php
namespace Formapro\MessageQueue\Router;

use Formapro\MessageQueue\Transport\MessageInterface;

interface RecipientListRouterInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return \Traversable|Recipient[]
     */
    public function route(MessageInterface $message);
}
