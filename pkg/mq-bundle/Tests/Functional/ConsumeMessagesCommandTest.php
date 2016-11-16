<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\Jms\JMSContext;
use Formapro\Jms\Message;
use Formapro\MessageQueue\Client\Config;
use Formapro\MessageQueue\Client\ConsumeMessagesCommand;
use Formapro\MessageQueue\Client\ContainerAwareMessageProcessorRegistry;
use Formapro\MessageQueue\Client\DelegateMessageProcessor;
use Formapro\MessageQueue\Client\MessageProducer;
use Formapro\MessageQueue\Client\Meta\DestinationMetaRegistry;
use Formapro\MessageQueue\Client\Router;
use Formapro\MessageQueue\Client\TopicSubscriberInterface;
use Formapro\MessageQueue\Consumption\ChainExtension;
use Formapro\MessageQueue\Consumption\Extension\LimitConsumedMessagesExtension;
use Formapro\MessageQueue\Consumption\Extension\LimitConsumptionTimeExtension;
use Formapro\MessageQueue\Consumption\MessageProcessorInterface;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Formapro\MessageQueue\Consumption\Result;
use Formapro\MessageQueue\Router\RouteRecipientListProcessor;
use Formapro\Stomp\Client\StompDriver;
use Formapro\Stomp\Test\StompExtensionTrait;
use Formapro\Stomp\Transport\StompContext;
use Formapro\Stomp\Transport\StompMessage;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;

class ConsumeMessagesCommandTest extends WebTestCase
{
    use StompExtensionTrait;

    /**
     * @var StompContext
     */
    private $stompContext;

    public function setUp()
    {
        $this->stompContext = $this->buildStompContext();

        $this->removeQueue('stomp.test-destination');
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $command = $this->container->get('formapro_message_queue.client.consume_messages_command');

        $this->assertInstanceOf(ConsumeMessagesCommand::class, $command);
    }

    public function testShouldConsumeMessage()
    {
        $container = new Container();

        $messageProcessor = new TestClientMessageProcessor();
        $container->set('message-processor', $messageProcessor);

        $processorRegistry = new ContainerAwareMessageProcessorRegistry();
        $processorRegistry->setContainer($container);

        $delegateMessageProcessor = new DelegateMessageProcessor($processorRegistry);

        $meta = [
            'test-destination' => [
                'subscribers' => ['message-processor'],
            ],
        ];

        $config = new Config('stomp', 'router-processor', 'test-destination', 'test-destination');

        $metaRegistry = new DestinationMetaRegistry($config, $meta, 'test-destination');

        $queueConsumer = new QueueConsumer(
            $this->stompContext,
            new ChainExtension([
                new LimitConsumedMessagesExtension(2),
                new LimitConsumptionTimeExtension(new \DateTime('+10 sec')),
            ])
        );

        $driver = new StompDriver($this->stompContext, $config);

        $router = new Router($driver, $metaRegistry);
        $router->addRoute('test-destination', 'message-processor', 'test-destination');
        $routerProcessor = new RouteRecipientListProcessor($router);
        $container->set('router-processor', $routerProcessor);

        $processorRegistry->set('router-processor', 'router-processor');
        $processorRegistry->set('message-processor', 'message-processor');

        $producer = new MessageProducer($driver);
        $producer->send('test-destination', 'test body');

        $command = new ConsumeMessagesCommand(
            $queueConsumer,
            $delegateMessageProcessor,
            $metaRegistry,
            $driver
        );

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertInstanceOf(StompMessage::class, $messageProcessor->message);
        $this->assertEquals('test body', $messageProcessor->message->getBody());
    }
}

class TestClientMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    public $message;

    public function process(Message $message, JMSContext $context)
    {
        $this->message = $message;

        return Result::ACK;
    }

    public static function getSubscribedTopics()
    {
        return ['test-destination'];
    }
}
