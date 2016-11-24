<?php

$autoload = null;
foreach ([__DIR__.'/../vendor/autoload.php', __DIR__.'/../../../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        $autoload = $file;

        break;
    }
}

if ($autoload) {
    require_once $autoload;
} else {
    throw new \LogicException('Composer autoload was not found');
}

//Establish connection to AMQP
$connection = new AMQPConnection();
$connection->setHost(getenv('SYMFONY__RABBITMQ__HOST'));
$connection->setLogin(getenv('SYMFONY__RABBITMQ__USER'));
$connection->setPassword(getenv('SYMFONY__RABBITMQ__PASSWORD'));
$connection->setVhost(getenv('SYMFONY__RABBITMQ__VHOST'));
$connection->setPort(getenv('SYMFONY__RABBITMQ__AMQP__PORT'));

\Formapro\MessageQueue\Test\RabbitmqAmqpExtension::tryConnect($connection, 1);

$context = new \Formapro\AmqpExt\AmqpContext($connection);

$queue = $context->createQueue('foo');
$fooConsumer = $context->createConsumer($queue);

$queue = $context->createQueue('bar');
$barConsumer = $context->createConsumer($queue);

$consumer = $context->createConsumer($queue);

$fooConsumer->receive(1);
$barConsumer->receive(1);

$consumers = [$fooConsumer, $barConsumer];

$consumer = $consumers[rand(0, 1)];

while (true) {
    if ($m = $consumer->receive(1)) {
        $consumer = $consumers[rand(0, 1)];
        $consumer->acknowledge($m);
    }
}

echo 'Done'."\n";
