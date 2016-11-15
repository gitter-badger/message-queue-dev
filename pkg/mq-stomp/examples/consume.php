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

use Formapro\Stomp\Transport\BufferedStompClient;
use Formapro\Stomp\Transport\StompContext;
use Stomp\Exception\ErrorFrameException;

$url = 'tcp://localhost:61613';
$login = 'guest';
$password = 'guest';
$vhost = '/';

try {
    $stomp = new BufferedStompClient($url);
    $stomp->setLogin($login, $password);
    $stomp->setVhostname($vhost);

    $context = new StompContext($stomp);

    $destination = $context->createQueue('destination');
    $destination->setDurable(true);
    $destination->setAutoDelete(false);

    $consumer = $context->createConsumer($destination);

    while (true) {
        if ($message = $consumer->receive()) {
            $consumer->acknowledge($message);

            var_dump($message->getBody());
            var_dump($message->getProperties());
            var_dump($message->getHeaders());
            echo '-------------------------------------'.PHP_EOL;
        }
    }
} catch (ErrorFrameException $e) {
    var_dump($e->getFrame());
}
