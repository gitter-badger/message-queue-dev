<?php
namespace FormaPro\MessageQueue\Transport\Null;

use FormaPro\MessageQueue\Transport\ConnectionInterface;

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
