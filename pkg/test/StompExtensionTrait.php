<?php
namespace Formapro\MessageQueue\Test;

use Formapro\Stomp\BufferedStompClient;
use Formapro\Stomp\StompContext;
use Stomp\Client;
use Stomp\Exception\ConnectionException;

trait StompExtensionTrait
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

    /**
     * @param string $queueName
     */
    private function removeQueue($queueName)
    {
        $rabbitmqHost = getenv('SYMFONY__RABBITMQ__HOST');
        $rabbitmqUser = getenv('SYMFONY__RABBITMQ__USER');
        $rabbitmqPassword = getenv('SYMFONY__RABBITMQ__PASSWORD');
        $rabbitmqVhost = getenv('SYMFONY__RABBITMQ__VHOST');

        $url = sprintf(
            'http://%s:15672/api/queues/%s/%s',
            $rabbitmqHost,
            urlencode($rabbitmqVhost),
            $queueName
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $rabbitmqUser.':'.$rabbitmqPassword);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type' => 'application/json',
        ]);
        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if (false == in_array($httpCode, [204, 404])) {
            throw new \LogicException('Failed to remove queue. The response status is '.$httpCode);
        }
    }
}