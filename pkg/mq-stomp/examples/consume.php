<?php
require_once(__DIR__.'/../vendor/autoload.php');

use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompConnection;
use Stomp\Exception\ErrorFrameException;

$url = 'tcp://localhost:61613';
$login = 'guest';
$password = 'guest';
$vhost = '/';

try {
    $stomp = new BufferedStompClient($url);
    $stomp->setLogin($login, $password);
    $stomp->setVhostname($vhost);

    $connection = new StompConnection($stomp);

    $context = $connection->createSession();

    $consumers = [];
    $count = 100;

    for ($i = 1; $i <= $count; $i++) {
        $destination = $context->createQueue('destination' . $i);
        $destination->setDurable(true);
        $destination->setAutoDelete(false);

        $consumer = $context->createConsumer($destination);
        $consumer->setPrefetchCount(100);

        $consumers[$i] = $consumer;
    }

    while (true) {
        for ($i = 1; $i <= $count; $i++) {
            $consumer = $consumers[$i];

            if ($message = $consumer->receive(0.001)) {
                $consumer->acknowledge($message);
                echo $i . ':' . $message->getBody() . PHP_EOL;
                if ($i != $message->getBody()) {
                    break;
                }
            }
        }
    }
} catch (ErrorFrameException $e) {
    var_dump($e->getFrame());
}
