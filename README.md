MessageQueue
============

The component incorporates message queue in your application via different transports.
It contains several layers.

The lowest layer is called Transport and provides an abstraction of transport protocol.
The Consumption layer provides tools to consume messages, such as cli command, signal handling, logging, extensions.
It works on top of transport layer.

The Client layer provides ability to start producing\consuming messages with as less as possible configuration.

Usage
-----

This is a complete example of message producing using only a transport layer:

```php
<?php

use FormaPro\MessageQueue\Transport\Amqp\AmqpConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;

$host = 'localhost';
$port = 5672;
$user = 'guest';
$password = 'guest';
$vhost = '/';

$amqpConnection = new AMQPLazyConnection($host, $port, $user, $password, $vhost);
$connection = new AmqpConnection($amqpConnection);

$session = $connection->createSession();

$queue = $session->createQueue('aQueue');
$message = $session->createMessage('Something has happened');

$session->createProducer()->send($queue, $message);

$session->close();
$connection->close();
```

This is a complete example of message consuming using only a transport layer:

```php
<?php

use FormaPro\MessageQueue\Transport\Amqp\AmqpConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;

$host = 'localhost';
$port = 5672;
$user = 'guest';
$password = 'guest';
$vhost = '/';

$amqpConnection = new AMQPLazyConnection($host, $port, $user, $password, $vhost);
$connection = new AmqpConnection($amqpConnection);

$session = $connection->createSession();

$queue = $session->createQueue('aQueue');
$consumer = $session->createConsumer($queue);

while (true) {
    if ($message = $consumer->receive()) {
        echo $message->getBody();

        $consumer->acknowledge($message);
    }
}

$session->close();
$connection->close();
```

This is a complete example of message consuming using consumption layer:

```php
<?php
use FormaPro\MessageQueue\Consumption\MessageProcessor;

class FooMessageProcessor implements MessageProcessor
{
    public function process(Message $message, Session $session)
    {
        echo $message->getBody();

        return self::ACK;
    }
}
```

```php
<?php

use FormaPro\MessageQueue\Consumption\ChainExtension;
use FormaPro\MessageQueue\Consumption\QueueConsumer;
use FormaPro\MessageQueue\Transport\Amqp\AmqpConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;

$host = 'localhost';
$port = 5672;
$user = 'guest';
$password = 'guest';
$vhost = '/';

$amqpConnection = new AMQPLazyConnection($host, $port, $user, $password, $vhost);
$connection = new AmqpConnection($amqpConnection);

$queueConsumer = new QueueConsumer($connection, new ChainExtension([]));
$queueConsumer->bind('aQueue', new FooMessageProcessor());

try {
    $queueConsumer->consume();
} finally {
    $queueConsumer->getConnection()->close();
}
```
