<?php
namespace Formapro\MessageQueue\Tests\Unit\Consumption\Mock;

use Formapro\MessageQueue\Consumption\Context;
use Formapro\MessageQueue\Consumption\ExtensionInterface;
use Formapro\MessageQueue\Consumption\EmptyExtensionTrait;

class BreakCycleExtension implements ExtensionInterface
{
    use EmptyExtensionTrait;

    protected $cycles = 1;

    private $limit;

    public function __construct($limit)
    {
        $this->limit = $limit;
    }

    public function onPostReceived(Context $context)
    {
        $this->onIdle($context);
    }

    public function onIdle(Context $context)
    {
        if ($this->cycles >= $this->limit) {
            $context->setExecutionInterrupted(true);
        } else {
            $this->cycles++;
        }
    }
}
