# Message queue bundle. Quick tour.

The bundle integrates MessageQueue component.
It adds easy to use configuration layer, register services and tie them together, register handy cli commands.

## Install

```bash
$ composer require formapro/message-queue-bundle formapro/amqp-ext
```

## Usage

First, you have to configure a transport layer and set one to be default.

```yaml
# app/config/config.yml

formapro_message_queue:
    transport:
        default: 'amqp'
        amqp:
            host: 'localhost'
            port: 5672
            user: 'guest'
            password: 'guest'
            vhost: '/'
    client: ~
```

Once you configured everything you can start producing messages:

```php
<?php
use Formapro\MessageQueue\Client\MessageProducer;

/** @var MessageProducer $messageProducer **/
$messageProducer = $container->get('formapro_message_queue.message_producer');

$messageProducer->send('aFooTopic', 'Something has happened');
```

To consume messages you have to first create a message processor:

```php
<?php
use Formapro\Fms\Message;
use Formapro\Fms\Context;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Client\TopicSubscriberInterface;

class FooMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    public function process(Message $message, Context $session)
    {
        echo $message->getBody();

        return Result::ACK;
        // return Result::REJECT; // when the message is broken
        // return Result::REQUEUE; // the message is fine but you want to postpone processing
    }

    public static function getSubscribedTopics()
    {
        return ['aFooTopic'];
    }
}
```

Register it as a container service and subscribe to the topic:

```yaml
foo_message_processor:
    class: 'FooMessageProcessor'
    tags:
        - { name: 'formapro_message_queue.client.message_processor' }
```

Now you can start consuming messages:

```bash
$ ./app/console formapro:message-queue:consume
```

_**Note**: Add -vvv to find out what is going while you are consuming messages. There is a lot of valuable debug info there.


[back to index](../index.md)
