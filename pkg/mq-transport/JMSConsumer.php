<?php
namespace Formapro\MessageQueue\Transport;


interface JMSConsumer
{
    /**
     * @param int|float $timeout
     *
     * @return Message
     */
    public function receive($timeout);

    /**
     * @return Message
     */
    public function receiveNoWait();

    /**
     * @return void
     */
    public function close();

    /**
     * @param MessageListener $listener
     *
     * @return void
     */
    public function setMessageListener(MessageListener $listener);

    /**
     * @return MessageListener
     */
    public function getMessageListener();
}
