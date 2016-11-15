<?php
namespace Formapro\MessageQueueBundle\Consumption\Extension;

use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DoctrineClearIdentityMapExtension implements ExtensionInterface
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
        foreach ($this->registry->getManagers() as $name => $manager) {
            $context->getLogger()->debug(sprintf(
                '[DoctrineClearIdentityMapExtension] Clear identity map for manager "%s"',
                $name
            ));

            $manager->clear();
        }
    }
}
