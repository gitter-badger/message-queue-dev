<?php
namespace Formapro\MessageQueue\Jms;

interface DeliveryMode
{
    const NON_PERSISTENT = 'jms.delivery_mode.non_persistent';
    const PERSISTENT = 'jms.delivery_mode.persistent';
}
