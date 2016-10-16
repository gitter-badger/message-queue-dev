<?php
namespace Formapro\MessageQueue\Client;

use Formapro\MessageQueue\Client\Meta\DestinationMetaRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateQueuesCommand extends Command
{
    /**
     * @var DestinationMetaRegistry
     */
    private $destinationMetaRegistry;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param DestinationMetaRegistry $destinationMetaRegistry
     * @param DriverInterface         $driver
     */
    public function __construct(DestinationMetaRegistry $destinationMetaRegistry, DriverInterface $driver)
    {
        parent::__construct('fp:message-queue:create-queues');

        $this->destinationMetaRegistry = $destinationMetaRegistry;
        $this->driver = $driver;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Creates all required queues');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->destinationMetaRegistry->getDestinationsMeta() as $meta) {
            $output->writeln(sprintf('Creating queue: <comment>%s</comment>', $meta->getTransportName()));

            $this->driver->createQueue($meta->getTransportName());
        }
    }
}
