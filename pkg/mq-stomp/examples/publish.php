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

use Formapro\Stomp\StompConnectionFactory;
use Stomp\Exception\ErrorFrameException;

$config = [
    'uri' => sprintf('tcp://%s:%s', getenv('SYMFONY__RABBITMQ__HOST'), getenv('SYMFONY__RABBITMQ__STOMP__PORT')),
    'login' => getenv('SYMFONY__RABBITMQ__USER'),
    'password' => getenv('SYMFONY__RABBITMQ__PASSWORD'),
    'vhost' => getenv('SYMFONY__RABBITMQ__VHOST'),
    'sync' => true,
];

try {
    $factory = new StompConnectionFactory($config);
    $context = $factory->createContext();

    $destination = $context->createQueue('destination');
    $destination->setDurable(true);
    $destination->setAutoDelete(false);

    $producer = $context->createProducer();

    $i = 1;
    while (true) {
        $message = $context->createMessage('payload: '.$i++);
        $producer->send($destination, $message);
        usleep(1000);
    }
} catch (ErrorFrameException $e) {
    var_dump($e->getFrame());
}
