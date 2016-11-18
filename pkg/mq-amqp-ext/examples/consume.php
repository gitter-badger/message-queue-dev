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
$connection->connect();

$context = new \Formapro\AmqpExt\AmqpContext($connection);

$queue = $context->createQueue('test.amqp.queue');

$consumer = $context->createConsumer($queue);

$message = $context->createConsumer($queue)->receive(10);
//$consumer->acknowledge($message);

var_dump($message);

echo 'Done'."\n";
