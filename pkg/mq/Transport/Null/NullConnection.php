<?php
namespace Formapro\MessageQueue\Transport\Null;

use Formapro\MessageQueue\Transport\ConnectionInterface;

class NullConnection implements ConnectionInterface
{
    /**
     * {@inheritdoc}
     *
     * @return NullSession
     */
    public function createSession()
    {
        return new NullSession();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }
}
