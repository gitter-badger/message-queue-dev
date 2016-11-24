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

$topic = $context->createTopic('test.amqp.ext');
$topic->addFlag(AMQP_DURABLE);
$topic->setType(AMQP_EX_TYPE_FANOUT);
$topic->setArguments(['alternate-exchange' => 'foo']);

$context->deleteTopic($topic);
$context->declareTopic($topic);

$fooQueue = $context->createQueue('foo');
$fooQueue->addFlag(AMQP_DURABLE);

$context->deleteQueue($fooQueue);
$context->declareQueue($fooQueue);

$context->bind($topic, $fooQueue);

$barQueue = $context->createQueue('bar');
$barQueue->addFlag(AMQP_DURABLE);

$context->deleteQueue($barQueue);
$context->declareQueue($barQueue);

$context->bind($topic, $barQueue);

$message = $context->createMessage('Hello Bar!');

while (true) {
    $context->createProducer()->send($fooQueue, $message);
//    $context->createProducer()->send($barQueue, $message);
}

echo 'Done'."\n";
