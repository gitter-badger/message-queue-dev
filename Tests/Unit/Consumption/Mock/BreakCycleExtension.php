<?php
namespace FormaPro\MessageQueue\Tests\Unit\Consumption\Mock;

use FormaPro\MessageQueue\Consumption\AbstractExtension;
use FormaPro\MessageQueue\Consumption\Context;

class BreakCycleExtension extends AbstractExtension
{
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
