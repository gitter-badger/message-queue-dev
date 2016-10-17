<?php
namespace Formapro\MessageQueue\Tests\Unit\Consumption\Mock;

use Formapro\MessageQueue\Consumption\AbstractExtension;
use Formapro\MessageQueue\Consumption\Context;

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
