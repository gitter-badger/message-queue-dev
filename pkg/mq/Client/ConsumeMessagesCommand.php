<?php
namespace Formapro\MessageQueue\Client;

use Formapro\MessageQueue\Client\Meta\DestinationMetaRegistry;
use Formapro\MessageQueue\Consumption\ChainExtension;
use Formapro\MessageQueue\Consumption\Extension\LoggerExtension;
use Formapro\MessageQueue\Consumption\LimitsExtensionsCommandTrait;
use Formapro\MessageQueue\Consumption\QueueConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeMessagesCommand extends Command
{
    use LimitsExtensionsCommandTrait;

    /**
     * @var QueueConsumer
     */
    private $consumer;

    /**
     * @var DelegateMessageProcessor
     */
    private $processor;

    /**
     * @var DestinationMetaRegistry
     */
    private $destinationMetaRegistry;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param QueueConsumer            $consumer
     * @param DelegateMessageProcessor $processor
     * @param DestinationMetaRegistry  $destinationMetaRegistry
     * @param DriverInterface          $driver
     */
    public function __construct(
        QueueConsumer $consumer,
        DelegateMessageProcessor $processor,
        DestinationMetaRegistry $destinationMetaRegistry,
        DriverInterface $driver
    ) {
        parent::__construct('formapro:message-queue:consume');

        $this->consumer = $consumer;
        $this->processor = $processor;
        $this->destinationMetaRegistry = $destinationMetaRegistry;
        $this->driver = $driver;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->configureLimitsExtensions();

        $this
            ->setDescription('A client\'s worker that processes messages. '.
                'By default it connects to default queue. '.
                'It select an appropriate message processor based on a message headers')
            ->addArgument('clientDestinationName', InputArgument::OPTIONAL, 'Queues to consume messages from')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($clientDestinationName = $input->getArgument('clientDestinationName')) {
            $queue = $this->driver->createQueue(
                $this->destinationMetaRegistry->getDestinationMeta($clientDestinationName)->getTransportName()
            );

            $this->consumer->bind($queue, $this->processor);
        } else {
            foreach ($this->destinationMetaRegistry->getDestinationsMeta() as $destinationMeta) {
                $queue = $this->driver->createQueue($destinationMeta->getTransportName());

                $this->consumer->bind($queue, $this->processor);
            }
        }

        $extensions = $this->getLimitsExtensions($input, $output);
        array_unshift($extensions, new LoggerExtension(new ConsoleLogger($output)));

        $runtimeExtensions = new ChainExtension($extensions);

        try {
            $this->consumer->consume($runtimeExtensions);
        } finally {
            $this->consumer->getContext()->close();
        }
    }
}
