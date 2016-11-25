<?php
namespace Formapro\Stomp;

use Formapro\Fms\ConnectionFactory;
use Stomp\Network\Connection;

class StompConnectionFactory implements ConnectionFactory
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var BufferedStompClient
     */
    private $stomp;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_replace([
            'uri' => null,
            'login' => null,
            'password' => null,
            'vhost' => null,
            'buffer_size' => 1000,
            'connection_timeout' => 1,
            'sync' => false,
        ], $config);
    }

    /**
     * {@inheritdoc}
     *
     * @return StompContext
     */
    public function createContext()
    {
        if (false == $this->stomp) {
            $config = $this->config;

            $connection = new Connection($config['uri'], $config['connection_timeout']);

            $this->stomp = new BufferedStompClient($connection, $config['buffer_size']);
            $this->stomp->setLogin($config['login'], $config['password']);
            $this->stomp->setVhostname($config['vhost']);
            $this->stomp->setSync($config['sync']);

            $this->stomp->connect();
        }

        return new StompContext($this->stomp);
    }
}
