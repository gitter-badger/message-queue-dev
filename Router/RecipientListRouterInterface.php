<?php
namespace FormaPro\MessageQueue\Router;

use FormaPro\MessageQueue\Transport\MessageInterface;

interface RecipientListRouterInterface
{
    /**
     * @param MessageInterface $message
     *
     * @return \Traversable|Recipient[]
     */
    public function route(MessageInterface $message);
}
