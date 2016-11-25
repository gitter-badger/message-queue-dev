<?php
namespace Formapro\MessageQueue\Test;

use Formapro\Stomp\StompConnectionFactory;
use Formapro\Stomp\StompContext;
use Stomp\Exception\ConnectionException;

trait RabbitmqStompExtension
{
    /**
     * @return StompContext
     */
    private function buildStompContext()
    {
        if (false == getenv('SYMFONY__RABBITMQ__HOST')) {
            throw new \PHPUnit_Framework_SkippedTestError('Functional tests are not allowed in this environment');
        }

        $rabbitmqHost = getenv('SYMFONY__RABBITMQ__HOST');
        $rabbitmqUser = getenv('SYMFONY__RABBITMQ__USER');
        $rabbitmqPort = getenv('SYMFONY__RABBITMQ__STOMP__PORT');
        $rabbitmqPassword = getenv('SYMFONY__RABBITMQ__PASSWORD');
        $rabbitmqVhost = getenv('SYMFONY__RABBITMQ__VHOST');

        return $this->attemptCreateContext([
            'uri' => "tcp://$rabbitmqHost:$rabbitmqPort",
            'login' => $rabbitmqUser,
            'password' => $rabbitmqPassword,
            'vhost' => $rabbitmqVhost,
            'sync' => true,
        ], 1);
    }

    private function attemptCreateContext(array $config, $attempt)
    {
        try {
            return (new StompConnectionFactory($config))->createContext();
        } catch (ConnectionException $e) {
            if ($attempt > 5) {
                throw $e;
            }
            sleep(1);

            ++$attempt;

            return $this->attemptCreateContext($config, $attempt);
        }
    }
}
