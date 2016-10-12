<?php
namespace FormaPro\MessageQueue\Client;

class MessagePriority
{
    const VERY_LOW = 'formapro.message_queue.client.very_low_message_priority';
    const LOW = 'formapro.message_queue.client.low_message_priority';
    const NORMAL = 'formapro.message_queue.client.normal_message_priority';
    const HIGH = 'formapro.message_queue.client.high_message_priority';
    const VERY_HIGH = 'formapro.message_queue.client.very_high_message_priority';
}
