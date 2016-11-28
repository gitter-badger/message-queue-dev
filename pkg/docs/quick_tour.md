# Quick tour
 
* (Transport)[#transport]
* (Consumption)[#consumption]
* (Remote Procedure Call (RPC))[#remote-procedure-call-rpc]
* (Job queue)[#job-queue]
* (Client)[#client]

## Transport

The transport layer or FMS (Formapro message service) is a Message Oriented Middleware for sending messages between two or more clients. 
It is a messaging component that allows applications to create, send, receive, and read messages. 
It allows the communication between different components of a distributed application to be loosely coupled, reliable, and asynchronous.

FMS is inspired by JMS (Java Message Service). We tried to be as close as possible to [JSR 914](https://docs.oracle.com/javaee/7/api/javax/jms/package-summary.html) specification.
For now it supports [AMQP](https://www.rabbitmq.com/tutorials/amqp-concepts.html) and [STOMP](https://stomp.github.io/) message queue protocols.
You can connect to many modern brokers such as [RabbitMQ](https://www.rabbitmq.com/), [ActiveMQ](http://activemq.apache.org/) and others. 

Produce a message:

```php
<?php
use Formapro\Fms\ConnectionFactory;

/** @var ConnectionFactory $connectionFactory **/
$fmsContext = $connectionFactory->createContext();

$destination = $fmsContext->createQueue('foo');
//$destination = $context->createTopic('foo');

$message = $fmsContext->createMessage('Hello world!');

$fmsContext->createProducer()->send($destination, $message);
```

Consume a message:

```php
<?php
use Formapro\Fms\ConnectionFactory;

/** @var ConnectionFactory $connectionFactory **/
$fmsContext = $connectionFactory->createContext();

$destination = $fmsContext->createQueue('foo');
//$destination = $context->createTopic('foo');

$consumer = $fmsContext->createConsumer($destination);

$message = $consumer->receive();

// process a message

$consumer->acknowledge($message);
// $consumer->reject($message);
```

## Consumption 

Consumption is a layer build on top of a transport functionality. 
The goal of the component is to simply message consumption. 
The `QueueConsumer` is main piece of the component it allows bind message processors (or callbacks) to queues. 
The `consume` method starts the consumption process which last as long as it is interrupted.

```php
<?php
use Formapro\Fms\Message;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Formapro\MessageQueue\Consumption\Result;

/** @var \Formapro\Fms\Context $fmsContext */
$fmsContext;

$queueConsumer = new QueueConsumer($fmsContext);

$queueConsumer->bind('foo_queue', function(Message $message) {
    // process messsage
    
    return Result::ACK;
});
$queueConsumer->bind('bar_queue', function(Message $message) {
    // process messsage
    
    return Result::ACK;
});

$queueConsumer->consume();
```

There are bunch of [extensions](consumption_extensions.md) available. 
This is an example of how you can add them. 
The `SignalExtension` provides support of process signals, whenever you send SIGTERM for example it will correctly managed.
The `LimitConsumptionTimeExtension` interrupts the consumption after given time. 

```php
<?php
use Formapro\MessageQueue\Consumption\ChainExtension;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Formapro\MessageQueue\Consumption\Extension\SignalExtension;
use Formapro\MessageQueue\Consumption\Extension\LimitConsumptionTimeExtension;

/** @var \Formapro\Fms\Context $fmsContext */
$fmsContext;

$queueConsumer = new QueueConsumer($fmsContext, new ChainExtension([
    new SignalExtension(),
    new LimitConsumptionTimeExtension(new \DateTime('now + 60 sec')),
]));
```

## Remote Procedure Call (RPC)

There is RPC component that allows you send RPC requests over MQ easily.
You can do several calls asynchronously. This is how you can send a RPC message and wait for a reply message.

```php
<?php
use Formapro\MessageQueue\Rpc\RpcClient;

/** @var \Formapro\Fms\Context $fmsContext */
$fmsContext;

$queue = $fmsContext->createQueue('foo');
$message = $fmsContext->createMessage('Hi there!');

$rpcClient = new RpcClient($fmsContext);

$promise = $rpcClient->callAsync($queue, $message, 1);
$replyMessage = $promise->getMessage();
```

There is also extensions for the consumption component. 
It simplifies a server side of RPC.

```php
<?php
use Formapro\Fms\Message;
use Formapro\Fms\Context;
use Formapro\MessageQueue\Consumption\ChainExtension;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Formapro\MessageQueue\Consumption\Extension\ReplyExtension;
use Formapro\MessageQueue\Consumption\Result;

/** @var \Formapro\Fms\Context $fmsContext */
$fmsContext;

$queueConsumer = new QueueConsumer($fmsContext, new ChainExtension([
    new ReplyExtension()
]));

$queueConsumer->bind('foo', function(Message $message, Context $context) {
    $replyMessage = $context->createMessage('Hello');
    
    return Result::reply($replyMessage);
});

$queueConsumer->consume();
```

## Client

It provides a high level abstraction.
The goal of the component is hide as much as possible details from you so you can concentrate on things that really matters. 
For example, It reduces a need to configure a broker.
It easy to use abstraction for producing and processing messages. 

## Job queue

There is job queue component build on top of a transport. It provides some additional features:

* Stores jobs to a database. So you can query that information and build a UI for it.
* Run unique job feature. If used guarantee that there is not any job with the same name running same time.
* Sub jobs. If used allow split a big job into smaller pieces and process them asynchronously and in parallel.
* Depended job. If used allow send a message when the whole job is finished (including sub jobs).  

[back to index](index.md)