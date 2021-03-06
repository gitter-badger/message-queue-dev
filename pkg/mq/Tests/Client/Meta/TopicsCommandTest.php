<?php
namespace Formapro\MessageQueue\Tests\Client\Meta;

use Formapro\MessageQueue\Client\Meta\TopicMetaRegistry;
use Formapro\MessageQueue\Client\Meta\TopicsCommand;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TopicsCommandTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldBeSubClassOfCommand()
    {
        $this->assertClassExtends(Command::class, TopicsCommand::class);
    }

    public function testCouldBeConstructedWithTopicMetaRegistryAsFirstArgument()
    {
        new TopicsCommand(new TopicMetaRegistry([]));
    }

    public function testShouldShowMessageFoundZeroTopicsIfAnythingInRegistry()
    {
        $command = new TopicsCommand(new TopicMetaRegistry([]));

        $output = $this->executeCommand($command);

        $this->assertContains('Found 0 topics', $output);
    }

    public function testShouldShowMessageFoundTwoTopics()
    {
        $command = new TopicsCommand(new TopicMetaRegistry([
            'fooTopic' => [],
            'barTopic' => [],
        ]));

        $output = $this->executeCommand($command);

        $this->assertContains('Found 2 topics', $output);
    }

    public function testShouldShowInfoAboutTopics()
    {
        $command = new TopicsCommand(new TopicMetaRegistry([
            'fooTopic' => ['description' => 'fooDescription', 'subscribers' => ['fooSubscriber']],
            'barTopic' => ['description' => 'barDescription', 'subscribers' => ['barSubscriber']],
        ]));

        $output = $this->executeCommand($command);

        $this->assertContains('fooTopic', $output);
        $this->assertContains('fooDescription', $output);
        $this->assertContains('fooSubscriber', $output);
        $this->assertContains('barTopic', $output);
        $this->assertContains('barDescription', $output);
        $this->assertContains('barSubscriber', $output);
    }

    /**
     * @param Command  $command
     * @param string[] $arguments
     *
     * @return string
     */
    protected function executeCommand(Command $command, array $arguments = [])
    {
        $tester = new CommandTester($command);
        $tester->execute($arguments);

        return $tester->getDisplay();
    }
}
