<?php
namespace Formapro\MessageQueue\Test;

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

        $extConnection = new \AMQPConnection();
        $extConnection->setHost(getenv('SYMFONY__RABBITMQ__HOST'));
        $extConnection->setPort(getenv('SYMFONY__RABBITMQ__AMQP__PORT'));
        $extConnection->setLogin(getenv('SYMFONY__RABBITMQ__USER'));
        $extConnection->setPassword(getenv('SYMFONY__RABBITMQ__PASSWORD'));
        $extConnection->setVhost(getenv('SYMFONY__RABBITMQ__VHOST'));

        self::tryConnect($extConnection, 1);

        return new AmqpContext(new \AMQPChannel($extConnection));
    }

    public static function tryConnect(\AMQPConnection $extConnection, $attempt)
    {
        try {
            $extConnection->connect();
        } catch (\AMQPConnectionException $e) {
            if ($attempt > 7) {
                throw $e;
            }
            sleep(1);

            ++$attempt;
            self::tryConnect($extConnection, $attempt);
        }
    }
}
