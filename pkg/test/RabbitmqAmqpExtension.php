<?php
namespace Formapro\MessageQueue\Test;

use Formapro\AmqpExt\AmqpConnectionFactory;
use Formapro\AmqpExt\AmqpContext;

trait RabbitmqAmqpExtension
{
    /**
     * @return AmqpContext
     */
    private function buildAmqpContext()
    {
        if (false == getenv('SYMFONY__RABBITMQ__HOST')) {
            throw new \PHPUnit_Framework_SkippedTestError('Functional tests are not allowed in this environment');
        }

        return self::attemptCreateContext([
            'host' => getenv('SYMFONY__RABBITMQ__HOST'),
            'port' => getenv('SYMFONY__RABBITMQ__AMQP__PORT'),
            'login' => getenv('SYMFONY__RABBITMQ__USER'),
            'password' => getenv('SYMFONY__RABBITMQ__PASSWORD'),
            'vhost' => getenv('SYMFONY__RABBITMQ__VHOST'),
        ], 1);
    }

    private function attemptCreateContext(array $config, $attempt)
    {
        try {
            return (new AmqpConnectionFactory($config))->createContext();
        } catch (\AMQPConnectionException $e) {
            if ($attempt > 5) {
                throw $e;
            }
            sleep(1);

            ++$attempt;

            return $this->attemptCreateContext($config, $attempt);
        }
    }
}
