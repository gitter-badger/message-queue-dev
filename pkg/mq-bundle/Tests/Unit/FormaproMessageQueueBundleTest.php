<?php
namespace Formapro\MessageQueueBundle\Tests\Unit;

use Formapro\MessageQueue\DependencyInjection\DefaultTransportFactory;
use Formapro\MessageQueue\DependencyInjection\NullTransportFactory;
use Formapro\MessageQueue\Test\ClassExtensionTrait;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildDestinationMetaRegistryPass;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildExtensionsPass;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildMessageProcessorRegistryPass;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildRouteRegistryPass;
use Formapro\MessageQueueBundle\DependencyInjection\Compiler\BuildTopicMetaSubscribersPass;
use Formapro\MessageQueueBundle\DependencyInjection\FormaproMessageQueueExtension;
use Formapro\MessageQueueBundle\FormaproMessageQueueBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FormaproMessageQueueBundleTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldExtendBundleClass()
    {
        $this->assertClassExtends(Bundle::class, FormaproMessageQueueBundle::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new FormaproMessageQueueBundle();
    }

    public function testShouldRegisterExpectedCompilerPasses()
    {
        $extensionMock = $this->createMock(FormaproMessageQueueExtension::class);

        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(BuildExtensionsPass::class))
        ;
        $container
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(BuildRouteRegistryPass::class))
        ;
        $container
            ->expects($this->at(2))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(BuildMessageProcessorRegistryPass::class))
        ;
        $container
            ->expects($this->at(3))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(BuildTopicMetaSubscribersPass::class))
        ;
        $container
            ->expects($this->at(4))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(BuildDestinationMetaRegistryPass::class))
        ;
        $container
            ->expects($this->at(5))
            ->method('getExtension')
            ->willReturn($extensionMock)
        ;

        $bundle = new FormaproMessageQueueBundle();
        $bundle->build($container);
    }

    public function testShouldRegisterDefaultAndNullTransportFactories()
    {
        $extensionMock = $this->createMock(FormaproMessageQueueExtension::class);

        $extensionMock
            ->expects($this->at(0))
            ->method('addTransportFactory')
            ->with($this->isInstanceOf(DefaultTransportFactory::class))
        ;
        $extensionMock
            ->expects($this->at(1))
            ->method('addTransportFactory')
            ->with($this->isInstanceOf(NullTransportFactory::class))
        ;

        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->at(5))
            ->method('getExtension')
            ->with('formapro_message_queue')
            ->willReturn($extensionMock)
        ;

        $bundle = new FormaproMessageQueueBundle();
        $bundle->build($container);
    }
}
