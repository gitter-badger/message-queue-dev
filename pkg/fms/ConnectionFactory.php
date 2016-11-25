<?php
namespace Formapro\Fms;

interface ConnectionFactory
{
    /**
     * @return Context
     */
    public function createContext();
}
