<?php
require_once(__DIR__.'/../../../vendor/autoload.php');

use Formapro\MessageQueueStompTransport\Transport\BufferedStompClient;
use Formapro\MessageQueueStompTransport\Transport\StompContext;
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

    $context = new StompContext($stomp);

    $destinations = [];
    $count = 100;

    for ($i = 1; $i <= $count; $i++) {
        $destination = $context->createQueue('destination' . $i);
        $destination->setDurable(true);
        $destination->setAutoDelete(false);

        $message = new StompMessage(''.$i);

        $destinations[] = [$destination, $message];
    }

    $producer = $context->createProducer();

    while (true) {
        foreach ($destinations as $destination) {
            echo sprintf('payload: "%s" destination: "%s"', $destination[1]->getBody(), $destination[0]->getStompName()) . PHP_EOL;
            $producer->send($destination[0], $destination[1]);
        }
    }
} catch (ErrorFrameException $e) {
    var_dump($e->getFrame());
}
