<?php
namespace Formapro\MessageQueueBundle\Tests\Functional\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Formapro\MessageQueueBundle\FormaproMessageQueueBundle(),
        ];

        return $bundles;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/FormaproMessageQueueBundle/cache';
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/FormaproMessageQueueBundle/cache/logs';
    }

    protected function getContainerClass()
    {
        return parent::getContainerClass().'BundleDefault';
    }

    /**
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
