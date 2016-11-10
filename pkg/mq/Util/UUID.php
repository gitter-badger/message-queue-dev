<?php
namespace Formapro\MessageQueue\Util;

class UUID
{
    /**
     * @return string
     */
    public static function generate()
    {
        return uniqid('', true);
    }
}
