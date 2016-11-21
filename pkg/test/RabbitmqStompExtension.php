<?php
namespace Formapro\MessageQueue\Test;

use Formapro\Stomp\BufferedStompClient;
use Formapro\Stomp\StompContext;
use Stomp\Client;
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

        $stomp = new BufferedStompClient("tcp://$rabbitmqHost:$rabbitmqPort");
        $stomp->setLogin($rabbitmqUser, $rabbitmqPassword);
        $stomp->setVhostname($rabbitmqVhost);

        $this->tryConnect($stomp, 1);

        return new StompContext($stomp);
    }

    private function tryConnect(Client $stomp, $attempt)
    {
        try {
            $stomp->connect();
        } catch (ConnectionException $e) {
            if ($attempt > 5) {
                throw $e;
            }
            sleep(1);

            ++$attempt;
            $this->tryConnect($stomp, $attempt);
        }
    }
}
