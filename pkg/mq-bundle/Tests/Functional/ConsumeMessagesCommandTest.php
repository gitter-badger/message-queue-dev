<?php
namespace Formapro\MessageQueueBundle\Tests\Functional;

use Formapro\MessageQueue\Client\ConsumeMessagesCommand;
use Formapro\MessageQueueBundle\Tests\Functional\App\StompAppKernel;
use Formapro\Stomp\Test\StompExtensionTrait;
use Formapro\Stomp\Transport\StompContext;
use Formapro\Stomp\Transport\StompMessage;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeMessagesCommandTest extends WebTestCase
{
    use StompExtensionTrait;

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

    public function testShouldConsumeMessages()
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
