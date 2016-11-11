<?php
require_once(__DIR__.'/../vendor/autoload.php');

use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompContext;
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
        $message = $context->createMessage('payload: ' . $i++);
        $producer->send($destination, $message);
        usleep(1000);
    }
} catch (ErrorFrameException $e) {
    var_dump($e->getFrame());
}
