<?php
require_once(__DIR__.'/../vendor/autoload.php');

use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompConnection;
use Formapro\MessageQueueStompTransport\Transport\StompMessage;
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

    $connection = new StompConnection($stomp);

    $session = $connection->createSession();

    $destinations = [];
    $count = 100;

    for ($i = 1; $i <= $count; $i++) {
        $destination = $session->createQueue('destination' . $i);
        $destination->setDurable(true);
        $destination->setAutoDelete(false);

        $message = new StompMessage(''.$i);

        $destinations[] = [$destination, $message];
    }

    $producer = $session->createProducer();

    while (true) {
        foreach ($destinations as $destination) {
            $producer->send($destination[0], $destination[1]);
        }
    }
} catch (ErrorFrameException $e) {
    var_dump($e->getFrame());
}
