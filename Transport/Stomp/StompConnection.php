<?php
namespace Formapro\MessageQueue\Transport\Stomp;

use Formapro\MessageQueue\Transport\ConnectionInterface;

class StompConnection implements ConnectionInterface
{
    /**
     * @var BufferedStompClient
     */
    private $stomp;

    /**
     * @param BufferedStompClient $stomp
     */
    public function __construct(BufferedStompClient $stomp)
    {
        $this->stomp = $stomp;
    }

    /**
     * {@inheritdoc}
     */
    public function createSession()
    {
        return new StompSession($this->stomp);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->stomp->disconnect();
    }
}
