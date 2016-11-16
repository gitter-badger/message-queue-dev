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

use Formapro\Stomp\BufferedStompClient;
use Formapro\Stomp\StompContext;
use Stomp\Exception\ErrorFrameException;

$url = 'tcp://localhost:61613';
$login = 'guest';
$password = 'guest';
$vhost = '/';

try {
    $stomp = new BufferedStompClient($url);
    $stomp->setLogin($login, $password);
    $stomp->setVhostname($vhost);
    $stomp->setSync(false);

    $context = new StompContext($stomp);

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
