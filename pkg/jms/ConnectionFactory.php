<?php
namespace Formapro\Jms;

interface ConnectionFactory
{
    /**
     * @return JMSContext
     */
    public function createContext();
}
