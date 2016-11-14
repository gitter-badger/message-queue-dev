<?php
namespace Formapro\Stomp\Test;

use Formapro\Stomp\Transport\BufferedStompClient;
use Formapro\Stomp\Transport\StompContext;

trait StompExtensionTrait
{
    /**
     * @return StompContext
     */
    private function buildStompContext()
    {
        if (false == getenv('RABBITMQ_HOST')) {
            throw new \PHPUnit_Framework_SkippedTestError('Functional tests are not allowed in this environment');
        }

        $rabbitmqHost = getenv('RABBITMQ_HOST');
        $rabbitmqUser = getenv('RABBITMQ_USER');
        $rabbitmqPort = getenv('RABBITMQ_STOMP_PORT');
        $rabbitmqPassword = getenv('RABBITMQ_PASSWORD');
        $rabbitmqVhost = getenv('RABBITMQ_VHOST');

        $stomp = new BufferedStompClient("tcp://$rabbitmqHost:$rabbitmqPort");
        $stomp->setLogin($rabbitmqUser, $rabbitmqPassword);
        $stomp->setVhostname($rabbitmqVhost);

        return new StompContext($stomp);
    }

    /**
     * @param string $queueName
     */
    private function removeQueue($queueName)
    {
        $rabbitmqHost = getenv('RABBITMQ_HOST');
        $rabbitmqUser = getenv('RABBITMQ_USER');
        $rabbitmqPassword = getenv('RABBITMQ_PASSWORD');
        $rabbitmqVhost = getenv('RABBITMQ_VHOST');

        $url = sprintf(
            'http://%s:15672/api/queues/%s/%s',
            $rabbitmqHost,
            urlencode($rabbitmqVhost),
            $queueName
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
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