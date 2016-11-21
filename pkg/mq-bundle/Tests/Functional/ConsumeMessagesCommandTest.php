<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\ConsumeMessagesCommand;
use Formapro\MessageQueue\Test\RabbitmqManagmentExtensionTrait;
use Formapro\MessageQueue\Test\RabbitmqStompExtension;
use Formapro\MessageQueueBundle\Tests\Functional\App\StompAppKernel;
use Formapro\Stomp\StompContext;
use Formapro\Stomp\StompMessage;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeMessagesCommandTest extends WebTestCase
{
    use RabbitmqStompExtension;
    use RabbitmqManagmentExtensionTrait;

    /**
     * @var StompContext
     */
    private $stompContext;

    public function setUp()
    {
        parent::setUp();

        $this->stompContext = $this->buildStompContext();

        $this->removeQueue('stomp.test');
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $command = $this->container->get('formapro_message_queue.client.consume_messages_command');

        $this->assertInstanceOf(ConsumeMessagesCommand::class, $command);
    }

    public function testClientConsumeMessagesCommandShouldConsumeMessage()
    {
        $command = $this->container->get('formapro_message_queue.client.consume_messages_command');
        $messageProcessor = $this->container->get('test.message.processor');

        $this->getMessageProducer()->send(TestMessageProcessor::TOPIC, 'test message body');

        $tester = new CommandTester($command);
        $tester->execute([
            '--message-limit' => 2,
            '--time-limit' => '+10sec',
        ]);

        $this->assertInstanceOf(StompMessage::class, $messageProcessor->message);
        $this->assertEquals('test message body', $messageProcessor->message->getBody());
    }

    public function testTransportConsumeMessagesCommandShouldConsumeMessage()
    {
        $command = $this->container->get('formapro_message_queue.command.consume_messages');
        $command->setContainer($this->container);
        $messageProcessor = $this->container->get('test.message.processor');

        $this->getMessageProducer()->send(TestMessageProcessor::TOPIC, 'test message body');

        $tester = new CommandTester($command);
        $tester->execute([
            '--message-limit' => 1,
            '--time-limit' => '+10sec',
            'queue' => 'stomp.test',
            'processor-service' => 'test.message.processor',
        ]);

        $this->assertInstanceOf(StompMessage::class, $messageProcessor->message);
        $this->assertEquals('test message body', $messageProcessor->message->getBody());
    }

    private function getMessageProducer()
    {
        return $this->container->get('formapro_message_queue.client.message_producer');
    }

    /**
     * @return string
     */
    public static function getKernelClass()
    {
        include_once __DIR__.'/app/StompAppKernel.php';

        return StompAppKernel::class;
    }
}
