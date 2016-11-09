<?php
namespace Formapro\MessageQueueBundle\Consumption\Extension;

use Doctrine\DBAL\Connection;
use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DoctrinePingConnectionExtension implements ExtensionInterface
{
    use EmptyExtensionTrait;

    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function onPreReceived(Context $context)
    {
        /** @var Connection $connection */
        foreach ($this->registry->getConnections() as $connection) {
            if ($connection->ping()) {
                return;
            }

            $context->getLogger()->debug(
                '[DoctrinePingConnectionExtension] Connection is not active trying to reconnect.'
            );

            $connection->close();
            $connection->connect();

            $context->getLogger()->debug(
                '[DoctrinePingConnectionExtension] Connection is active now.'
            );
        }
    }
}
