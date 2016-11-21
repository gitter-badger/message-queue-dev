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

        $amqp = new \AMQPConnection();
        $amqp->setHost(getenv('SYMFONY__RABBITMQ__HOST'));
        $amqp->setPort(getenv('SYMFONY__RABBITMQ__AMQP__PORT'));
        $amqp->setLogin(getenv('SYMFONY__RABBITMQ__USER'));
        $amqp->setPassword(getenv('SYMFONY__RABBITMQ__PASSWORD'));
        $amqp->setVhost(getenv('SYMFONY__RABBITMQ__VHOST'));

        self::tryConnect($amqp, 1);

        return new AmqpContext($amqp);
    }

    public static function tryConnect(\AMQPConnection $amqp, $attempt)
    {
        try {
            $amqp->connect();
        } catch (\AMQPConnectionException $e) {
            if ($attempt > 7) {
                throw $e;
            }
            sleep(1);

            ++$attempt;
            self::tryConnect($amqp, $attempt);
        }
    }
}
